# Interface Contract — Environment Variables

**Feature**: 001-self-hosted-runner  
**Branch**: `001-self-hosted-runner`  
**Generated**: 2026-03-19  
**Updated**: 2026-05-14 (DA-08: Docker credential forwarding variables added)

This document is the authoritative reference for all environment variables consumed by the runner container. It is the operator-facing interface contract (FR-020, FR-021, FR-022, FR-023).

---

## Required Variables

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `ACCESS_TOKEN` | `string` | _(none — required)_ | GitHub Personal Access Token. Must have `admin:org` scope for org-level runners or `repo` scope for repo-level runners. Must **not** be `<YOUR-GITHUB-ACCESS-TOKEN>` (placeholder detection). |
| `ORGANIZATION` | `string` | _(none — required)_ | GitHub organization name or user account name. Determines the API endpoint and URL used for runner registration. Must **not** be `<YOUR-GITHUB-ORGANIZATION>`. |

---

## Optional Variables

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `REPOSITORY` | `string` | _(empty)_ | Repository name (without org prefix). If set, the runner is registered at repo-level (`github.com/{ORGANIZATION}/{REPOSITORY}`). If empty, org-level registration is used. |
| `RUNNER_DIR` | `string (abs path)` | `/home/docker/actions-runner` | **DooD-critical**. The absolute path on the host where runner files and `_work/` are stored. This path MUST be identical on the host and inside the container (see Volumes contract). For Synology DSM, use `/volume1/docker/actions-runner` or similar `/volume1/...` path. |
| `RUNNER_NAME` | `string` | `$(hostname)` | Display name of the runner in GitHub Actions UI. Must be unique within the org/repo for reliable routing. |
| `RUNNER_LABELS` | `csv string` | `self-hosted,linux,x64` | Comma-separated labels used for `runs-on:` routing in workflows. `self-hosted` is always recommended as the first label. |
| `RUNNER_WORKDIR` | `string (rel path)` | `_work` | Relative path inside `RUNNER_DIR` used by the runner as its working directory. Rarely needs changing. |
| `EPHEMERAL` | `bool (true/false)` | `true` | If `true`, runner exits after completing one job and Docker Compose restarts it (clean slate per job — recommended for security). If `false`, runner accepts multiple jobs sequentially without restarting. |
| `ACTIONS_RUNNER_CONTAINER_NETWORK` | `string` | `host` | Docker network used by `container:` and `services:` job containers spawned via DooD. `host` shares the host network namespace. Set to a named Docker network for isolated deployments. |
| `DOCKER_CONFIG_HOST` | `string (abs path)` | _(empty)_ | **Docker credential forwarding (DA-08).** Host absolute path to a directory containing a valid `config.json` with Docker registry credentials (e.g. `~/.docker` or `/root/.docker`). When set, this directory is mounted read-only at `/home/docker/.docker` inside the container, giving the host Docker daemon access to credentials needed to pull private images (e.g. `ghcr.io/…`) during `container:` jobs. **Required if any job uses a private container image.** See also: volume contract below. |
| `GHCR_TOKEN` | `string` | _(empty)_ | **Alternative Docker credential mechanism (DA-08).** If set, `entrypoint.sh` Phase 1 executes `docker login ghcr.io` with this token before handing off to the runner. Less portable than `DOCKER_CONFIG_HOST` (GHCR-specific). Mutually exclusive with `DOCKER_CONFIG_HOST`; if both are set, `DOCKER_CONFIG_HOST` takes precedence. |

---

## Placeholder Detection

The following placeholder values in `.env.example` trigger a fast-fail at startup with an explicit error:

| Variable | Detected Placeholder |
|----------|---------------------|
| `ACCESS_TOKEN` | `<YOUR-GITHUB-ACCESS-TOKEN>` |
| `ORGANIZATION` | `<YOUR-GITHUB-ORGANIZATION>` |

If detected, the container exits with:
```
[runner] FATAL: ACCESS_TOKEN contains a placeholder value. Please set a real GitHub PAT in your .env file.
```

---

## Derived Values (internal, not operator-configurable)

| Internal Variable | Derived From | Value |
|-------------------|-------------|-------|
| `registration_url` | `ORGANIZATION`, `REPOSITORY` | `https://github.com/{ORG}[/{REPO}]` |
| `api_token_url` | `ORGANIZATION`, `REPOSITORY` | `https://api.github.com/orgs/{ORG}/actions/runners/registration-token` OR `https://api.github.com/repos/{ORG}/{REPO}/actions/runners/registration-token` |
| `ACTIONS_RUNNER_CONTAINER_HOOKS` | Hardcoded | `/opt/container-hooks/index.js` |
| `RUNNER_DIST` | Hardcoded | `/opt/runner-dist` (image layer — source for first-run staging) |

---

## Volumes Contract

| Mount | Host Path | Container Path | Required | Description |
|-------|-----------|----------------|----------|-------------|
| Docker socket | `/var/run/docker.sock` | `/var/run/docker.sock` | **Yes** | DooD access to host Docker daemon. Absence → fast-fail. |
| Runner workdir | `${RUNNER_DIR}` | `${RUNNER_DIR}` | **Yes** | **MUST be identical paths on both sides.** The runner binary, `_work/`, `_diag/`, `.credentials`, `.runner` files live here. Non-identical paths → fast-fail with diagnostic. |
| Docker credentials | `${DOCKER_CONFIG_HOST}` | `/home/docker/.docker` | **No** | Read-only mount. Required only when `container:` jobs use private registry images. Provides registry credentials to the host Docker daemon. Example: `-v /root/.docker:/home/docker/.docker:ro`. |

### ⚠️ Why `host_path == container_path` is mandatory

When a workflow uses `container:` or `services:`, the runner (inside container A) instructs
the **host Docker daemon** to mount `${RUNNER_DIR}/_work` into a new container B.
The host daemon resolves that path **on the host filesystem**, not inside container A.

- If host path = `/volume1/docker/actions-runner` but container path = `/home/docker/actions-runner`:
  the runner sends `/home/docker/actions-runner/_work` to the daemon → the host cannot find it → **bind mount error**.
- The only correct approach: the path inside the container must be exactly the host path.

**Default values by host type:**

| Host | `RUNNER_DIR` default | Correct volume mount |
|------|---------------------|---------------------|
| Ubuntu standard | `/home/docker/actions-runner` | `/home/docker/actions-runner:/home/docker/actions-runner` |
| Synology DSM | `/volume1/docker/actions-runner` | `/volume1/docker/actions-runner:/volume1/docker/actions-runner` |

### Standard Linux example
```yaml
volumes:
  - /var/run/docker.sock:/var/run/docker.sock
  - /home/docker/actions-runner:/home/docker/actions-runner  # host path == container path ✅
  # Optional: uncomment to forward Docker credentials for private images (DA-08)
  # - /root/.docker:/home/docker/.docker:ro
environment:
  RUNNER_DIR: /home/docker/actions-runner
  # DOCKER_CONFIG_HOST: /root/.docker   # optional, enables private image pulls
```

### Synology DSM example
```yaml
volumes:
  - /var/run/docker.sock:/var/run/docker.sock
  # ✅ /volume1/... on both sides — correct and intentional for Synology hosts
  # ❌ DO NOT use /home/docker/actions-runner as container path when host path is /volume1/...
  - /volume1/docker/actions-runner:/volume1/docker/actions-runner
  # Optional: forward Docker credentials for private images (DA-08)
  # - /root/.docker:/home/docker/.docker:ro
environment:
  RUNNER_DIR: /volume1/docker/actions-runner
  # DOCKER_CONFIG_HOST: /root/.docker   # optional, enables private image pulls
```

### ⚠️ Private image auth requirement (DA-08)

If any workflow uses `container: ghcr.io/<org>/<image>` (or any other private registry image), the host Docker daemon **must have credentials** to pull that image. Without credentials, the Pre Job Hook will fail with `docker exit 1` and Worker exit code 102.

**Option A — Credential file mount (recommended):**
```yaml
volumes:
  - ${DOCKER_CONFIG_HOST:-/root/.docker}:/home/docker/.docker:ro
```

**Option B — Token env var (GHCR only):**
```yaml
environment:
  GHCR_TOKEN: ${GHCR_TOKEN}   # entrypoint runs docker login ghcr.io at Phase 1
```

---

## Ports Contract

The runner container exposes **no ports**. All communication is outbound-only:

| Direction | Destination | Protocol | Purpose |
|-----------|------------|---------|---------|
| Outbound | `api.github.com:443` | HTTPS | Registration token API, runner polling |
| Outbound | `github.com:443` | HTTPS | Registration URL, job polling |
| Outbound | `ghcr.io:443` | HTTPS | Image pulls during jobs (if using GHCR) |
| Inbound | _(none)_ | — | No inbound ports required |

---

## Image Tags Contract

| Tag | Mutability | Description |
|-----|-----------|-------------|
| `latest` | Mutable (updated on each release) | Always points to the most recent stable build. Use for dev/homelab deployments. |
| `YYYY.MM.DD` (e.g. `2026.03.19`) | Immutable | Date-tagged release for production pinning. |
| `runner-v{VERSION}` (e.g. `runner-v2.334.0`) | Immutable | Tagged by embedded runner binary version. Useful for matching GitHub's runner requirements. |

**Registry**: `ghcr.io/abenevaut/self-hosted-github-runner`  
**Architecture**: `linux/amd64` only. No multi-arch manifest.

