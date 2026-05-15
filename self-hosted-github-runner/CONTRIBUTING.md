# Contributing — self-hosted-github-runner

## Runner Version Bump Procedure

> **Estimated time**: < 10 minutes  
> **Prerequisites**: `curl`, `sha256sum` (or `shasum -a 256`), Docker

### Step 1 — Check for a new runner release

```bash
curl -s https://api.github.com/repos/actions/runner/releases/latest \
  | jq -r .tag_name
```

Or browse [https://github.com/actions/runner/releases](https://github.com/actions/runner/releases).

### Step 2 — Download the tarball and compute its SHA256

```bash
NEW_VERSION=2.334.0   # replace with the target version

curl -fsSL \
  "https://github.com/actions/runner/releases/download/v${NEW_VERSION}/actions-runner-linux-x64-${NEW_VERSION}.tar.gz" \
  -o /tmp/actions-runner-linux-x64-${NEW_VERSION}.tar.gz

sha256sum /tmp/actions-runner-linux-x64-${NEW_VERSION}.tar.gz
# or on macOS: shasum -a 256 /tmp/actions-runner-linux-x64-${NEW_VERSION}.tar.gz
```

### Step 3 — Update Dockerfile ARGs

Edit `Dockerfile`:

```dockerfile
ARG RUNNER_VERSION="<NEW_VERSION>"
ARG RUNNER_SHA256="<SHA256_FROM_STEP_2>"
```

### Step 4 — Check container-hooks compatibility

Browse [https://github.com/actions/runner-container-hooks/releases](https://github.com/actions/runner-container-hooks/releases) and check the release notes for the version compatible with your new runner version.

If a newer version is required, update in `Dockerfile`:

```dockerfile
ARG CONTAINER_HOOKS_VERSION="<NEW_HOOKS_VERSION>"
```

### Step 5 — Build and validate locally

```bash
docker build --platform linux/amd64 -t self-hosted-github-runner:test .

# Verify architecture
docker image inspect self-hosted-github-runner:test | jq '.[0].Architecture'
# Expected: "amd64"

# Verify image size < 1.5 GB
docker image ls self-hosted-github-runner:test
```

### Step 6 — Open a Pull Request

1. Commit your Dockerfile changes on a feature branch.
2. Open a PR targeting `main`.
3. CI builds and validates the image.
4. After review and merge, the `publish.yml` GitHub Actions workflow pushes the new image to GHCR with tags `latest`, `YYYY.MM.DD`, and `runner-vX.Y.Z`.

---

## Development Workflow

### Prerequisites

- Docker ≥ 20.10 with Compose plugin
- A GitHub PAT with `admin:org` scope (for local testing)
- A Linux amd64 host (or a Linux amd64 VM / Docker Desktop with amd64 emulation for build only)

### Local test

```bash
# 1. Build
docker build --platform linux/amd64 -t gh-runner-dev .

# 2. Create a test runner directory
mkdir -p /tmp/test-runner

# 3. Run with test credentials
docker run --rm \
  -e ORGANIZATION=<YOUR-ORG> \
  -e ACCESS_TOKEN=<YOUR-PAT> \
  -e RUNNER_DIR=/tmp/test-runner \
  -e EPHEMERAL=true \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v /tmp/test-runner:/tmp/test-runner \
  gh-runner-dev
```

### Validating the CAUSE-01 / CAUSE-02 fixes

**CAUSE-01** (GHCR auth): Trigger a workflow with `container: ghcr.io/your-org/private-image:tag`. With `GHCR_TOKEN` set, the Pre Job Hook should succeed. Without it, the hook should fail with a clear credential error.

**CAUSE-02** (bind-mount paths): Trigger a workflow with `container: ubuntu:24.04`. The Pre Job Hook should create the job container without "Bind mount failed" errors. Check that `_work/_temp`, `_work/_actions`, `_work/_tool_cache` exist on the host before the job starts.

---

## Code Standards

- `entrypoint.sh`: `set -euo pipefail` in Phase 2; all exit paths echo an informative message with `[runner]` or `[entrypoint]` prefix.
- `docker-wrapper.sh`: `set -u`; all `mkdir -p` operations have a `sudo` fallback; all warnings/errors go to stderr with `[docker-wrapper]` prefix.
- `Dockerfile`: single-stage build, all `apt-get install` use `--no-install-recommends` and are followed by `apt-get clean && rm -rf /var/lib/apt/lists/*`.
- Shell scripts: UNIX line endings (LF), no trailing CRLF (the Dockerfile strips them with `sed -i 's/\r//'`).

---

## Security Checklist (before any release)

- [ ] `ACCESS_TOKEN` is never echoed in clear text in any log output
- [ ] `REG_TOKEN` is never stored to disk
- [ ] `EPHEMERAL=true` remains the default
- [ ] `USER root` is only active for Phase 1 of `entrypoint.sh`; Phase 2 runs as `docker` (UID 1000)
- [ ] No `--privileged` flag anywhere in the build or compose files
- [ ] SHA256 of the runner tarball is verified during `docker build`

