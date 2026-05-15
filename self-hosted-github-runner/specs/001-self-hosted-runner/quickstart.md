# Quickstart — Self-Hosted GitHub Actions Runner

**Feature**: 001-self-hosted-runner  
**Image**: `ghcr.io/abenevaut/self-hosted-github-runner:latest`  
**Architecture**: `linux/amd64` only

---

## Prerequisites

- Docker ≥ 20.10 with Docker Compose plugin
- Host architecture: amd64/x86_64
- Outbound HTTPS access to `github.com`, `api.github.com`, `ghcr.io`
- A GitHub Personal Access Token (PAT) with:
  - `admin:org` scope → organization-level runner
  - `repo` scope → repository-level runner

---

## ⚠️ DooD Path Requirement — Why both sides must be identical

> **This is not a bug. It is a fundamental property of Docker-outside-of-Docker (DooD).**

### How it works (3 steps)

```
Step 1: The runner container (A) is started with a bind-mount.
        Host path        → Container path
        /host/runner-dir → /container/runner-dir   ← these need to be the SAME

Step 2: When a workflow uses `container:` or `services:`, the runner (inside A)
        asks the HOST Docker daemon to start a new container (B), passing mount
        instructions like:
            "mount /container/runner-dir/_work into container B"
             ↑ this path is what the runner SEES inside container A

Step 3: The HOST daemon receives that instruction and looks for
        /container/runner-dir/_work on the HOST filesystem.
        If host path ≠ container path → the host finds NOTHING → BIND MOUNT ERROR ❌
```

### ASCII diagram

```
┌──────────────────────────────────────────────────────────────┐
│  HOST filesystem                                             │
│                                                              │
│  /volume1/docker/actions-runner/   ← real host path         │
│     └── _work/                                               │
│                                                              │
│  Docker daemon (host)                                        │
│   ├── Container A (gh-runner)                                │
│   │     sees: /volume1/docker/actions-runner/_work  ✅       │
│   │     (same as host → daemon can resolve it)               │
│   │                                                          │
│   └── Container B (job container, spawned by hooks)          │
│         gets: -v /volume1/docker/actions-runner/_work:/__w   │
│               ↑ daemon resolves this on HOST FS → FOUND ✅   │
└──────────────────────────────────────────────────────────────┘
```

### Golden rule

> **`host_path` MUST equal `container_path`** — you cannot use a "nice" fixed path
> inside the container while the real host path is different.

### Comparison table

| Host type | Host path | Container path | Volume mount | Status |
|-----------|-----------|----------------|--------------|--------|
| Ubuntu standard | `/home/docker/actions-runner` | `/home/docker/actions-runner` | `/home/docker/actions-runner:/home/docker/actions-runner` | ✅ Correct |
| Synology DSM | `/volume1/docker/actions-runner` | `/volume1/docker/actions-runner` | `/volume1/docker/actions-runner:/volume1/docker/actions-runner` | ✅ Correct |
| ❌ Wrong (any host) | `/volume1/docker/actions-runner` | `/home/docker/actions-runner` | `/volume1/docker/actions-runner:/home/docker/actions-runner` | ❌ FAILS at job time |

The entrypoint detects the mismatch at startup (before registering the runner) and exits with a clear diagnostic showing both paths and the fix.

---

## Quickstart: Standard Ubuntu/Linux Host

```bash
# 1. Clone the project
git clone https://github.com/abenevaut/self-hosted-github-runner.git
cd self-hosted-github-runner

# 2. Create the runner directory (identical path host ↔ container — DooD requirement)
sudo mkdir -p /home/docker/actions-runner
sudo chown $USER:$USER /home/docker/actions-runner

# 3. Configure environment
cp .env.example .env
# Edit .env: set ORGANIZATION and ACCESS_TOKEN

# 4. Start
docker compose up -d

# 5. Verify
docker compose logs -f gh-runner
# Expected: "[runner] Runner is registered and listening for jobs..."
# Then: check https://github.com/organizations/<YOUR-ORG>/settings/actions/runners
```

Full `docker-compose.yml` for Ubuntu (paths are identical host ↔ container, as required by DooD):

```yaml
services:
  gh-runner:
    image: ghcr.io/abenevaut/self-hosted-github-runner:latest
    container_name: gh-runner
    restart: always
    platform: linux/amd64
    environment:
      - ORGANIZATION=your-org-name
      - ACCESS_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
      # RUNNER_DIR default is /home/docker/actions-runner
      # The volume below MUST use the same path on both sides (DooD requirement)
      - RUNNER_DIR=/home/docker/actions-runner
      - EPHEMERAL=true
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      # ✅ host path == container path (required for DooD — see section above)
      - /home/docker/actions-runner:/home/docker/actions-runner
```

---

## Quickstart: Synology DSM (Container Manager)

> ⚠️ On Synology, the real storage path is `/volume1/...` — NOT `/home/docker/...`.  
> The volume mount below uses `/volume1/docker/actions-runner` on **both sides**,  
> which is **correct and intentional** (DooD path requirement — see section above).

```bash
# Via SSH into the Synology:
sudo mkdir -p /volume1/docker/actions-runner
```

In Container Manager → Projects → Create with compose:

```yaml
services:
  gh-runner:
    image: ghcr.io/abenevaut/self-hosted-github-runner:latest
    container_name: gh-runner
    restart: always
    platform: linux/amd64
    environment:
      - ORGANIZATION=your-org-name
      - ACCESS_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
      # RUNNER_DIR must match the host path exactly (DooD requirement)
      - RUNNER_DIR=/volume1/docker/actions-runner
      - EPHEMERAL=true
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      # ✅ /volume1/docker/actions-runner on BOTH sides — correct and intentional
      #    The Synology host FS uses /volume1/... so the container path must too.
      #    Using /home/docker/actions-runner as container path would BREAK DooD jobs.
      - /volume1/docker/actions-runner:/volume1/docker/actions-runner
```

---

## Environment Variables Reference

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `ORGANIZATION` | ✅ | — | GitHub org name |
| `ACCESS_TOKEN` | ✅ | — | GitHub PAT |
| `REPOSITORY` | ⬜ | _(empty → org-level)_ | For repo-level runners |
| `RUNNER_DIR` | ⬜ | `/home/docker/actions-runner` | Must match exactly host ↔ container |
| `RUNNER_NAME` | ⬜ | `$(hostname)` | Display name in GitHub UI |
| `RUNNER_LABELS` | ⬜ | `self-hosted,linux,x64` | Routing labels for `runs-on:` |
| `RUNNER_WORKDIR` | ⬜ | `_work` | Job workspace subdirectory |
| `EPHEMERAL` | ⬜ | `true` | One job per container instance |

---

## Startup Diagnostics — Common Errors

| Error message | Cause | Fix |
|--------------|-------|-----|
| `FATAL: /var/run/docker.sock is not mounted` | Missing volume | Add `-v /var/run/docker.sock:/var/run/docker.sock` |
| `FATAL: DooD path mismatch` | Host path ≠ container path | Set `RUNNER_DIR` to host path, mount `$RUNNER_DIR:$RUNNER_DIR` |
| `FATAL: ACCESS_TOKEN contains a placeholder` | `.env` not edited | Set a real PAT in `.env` |
| `Failed to obtain a registration token` | Invalid/expired PAT | Regenerate PAT with correct scopes |
| `ERROR: ${RUNNER_DIR} is not writable` | Permission denied | `sudo chown $USER $RUNNER_DIR` on host |

---

## SIGTERM / Clean Shutdown

```bash
docker compose down
# Runner deregisters from GitHub before stopping (< 30 s)
# Verify: runner disappears from GitHub Actions → Runners
```

---

## Updating the Runner Version

```bash
# Edit Dockerfile: update RUNNER_VERSION + RUNNER_SHA256
# Then rebuild:
docker compose build
docker compose up -d
```

Or pull the latest pre-built image:
```bash
docker compose pull
docker compose up -d
```

