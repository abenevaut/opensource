# Implementation Plan — Feature 001: Self-Hosted GitHub Actions Runner

**Feature Branch**: `001-self-hosted-runner`  
**Spec**: [`spec.md`](./spec.md)  
**Research**: [`research.md`](./research.md)  
**Data Model**: [`data-model.md`](./data-model.md)  
**Interface Contract**: [`contracts/interface.md`](./contracts/interface.md)  
**Quickstart**: [`quickstart.md`](./quickstart.md)  
**Generated**: 2026-03-19  
**Updated**: 2026-05-14 (diagnostic findings from `_diag/` log analysis)  
**Status**: Ready for implementation

---

## Technical Context

| Item | Value | Source |
|------|-------|--------|
| Base image | `ubuntu:24.04` (pinned, amd64 only) | FR-003, Dockerfile |
| Runner binary | `actions/runner v2.334.0` (SHA256 pinned) | Dockerfile ARG |
| Container hooks | `actions/runner-container-hooks v0.8.1` | Dockerfile ARG |
| Docker CLI inside image | `docker-ce-cli` + `docker-buildx-plugin` + `docker-compose-plugin` from Docker's official APT repo | FR-031, FR-032 |
| Runtime user | `docker` (UID 1000) — non-root for Phase 2 | FR-040 |
| Init user | `root` — Phase 1 only, then `exec sudo -u docker` | FR-040 |
| DooD mechanism | Host socket mount `/var/run/docker.sock:/var/run/docker.sock` | FR-031 |
| Path-equality constraint | `RUNNER_DIR` host path MUST equal container path | FR-033, FR-034 |
| Path-mismatch detection | `docker inspect` self on the host socket | research.md §6 |
| GID resolution | Dynamic: `stat -c '%g' /var/run/docker.sock` → `groupadd` + `usermod` | research.md §5 |
| Registration API | GitHub REST v3, `POST .../registration-token`, `Bearer` auth | research.md §8 |
| Retry on startup | 3 × 10 s fixed interval via `curl --retry 3 --retry-delay 10` | research.md §3 |
| EPHEMERAL default | `true` (GitHub recommendation, security-first) | research.md §1 |
| Auto-update | **Disabled** (`--disableupdate` flag to `config.sh`) | research.md §2 |
| Image registry | `ghcr.io/abenevaut/self-hosted-github-runner` | FR-060 |
| Tags strategy | `latest` (mutable) + `YYYY.MM.DD` + `runner-vX.Y.Z` (immutable) | FR-061 |
| Build platform | `linux/amd64` only — no multi-arch manifest | FR-001 |

---

## Constitution Check

> No `.specify/memory/constitution.md` found in this project. Section populated from spec FR/SC gates.

| Gate | Status | Note |
|------|--------|------|
| No cross-job secret leakage | ✅ | `EPHEMERAL=true` default; token not written to volume |
| Non-root runtime | ✅ | Phase 2 runs as `docker` user |
| Token not logged | ✅ | `ACCESS_TOKEN` masked in all `echo` statements |
| No rootless/DinD | ✅ | DooD only, as specified |
| amd64-only | ✅ | `platform: linux/amd64` in compose + single-arch build |

---

## Diagnostic Findings — Root Cause Analysis (2026-05-14)

> Source: `_diag/` log analysis on a real `Ubuntu 24.04.4 LTS` host running `Runner v2.334.0`.

### What Works ✅

| Component | Status |
|-----------|--------|
| Runner startup on `Ubuntu 24.04.4 LTS` (linux-x64) | ✅ |
| Runner v2.334.0 registers to GitHub → appears `Idle` | ✅ |
| Runner receives the job correctly | ✅ |
| Worker process is spawned (`Runner.Worker`) | ✅ |

### What Fails ❌

**Log `Worker_20260514-184225-utc.log` — confirmed error:**
```json
{
  "action": "Pre Job Hook",
  "stage": "Pre",
  "result": "failed",
  "errorMessages": [
    "Error: The process '/usr/local/bin/docker' failed with exit code 1",
    "Process completed with exit code 1.",
    "Executing the custom container implementation failed. Please contact your self hosted runner administrator."
  ],
  "containerHookData": "{\"hookScriptPath\":\"/opt/container-hooks/index.js\"}"
}
```

**Worker exit code 102** across all runs → specific code = container hook failure in `actions/runner`.

**Job context:**
- `jobContainer.image`: `ghcr.io/abenevaut/vapor-ci:php84` (GHCR image, potentially private)
- Hook `/opt/container-hooks/index.js` calls `/usr/local/bin/docker` (= our `docker-wrapper.sh`)
- The `docker` CLI inside the container fails with exit code 1

**Failure sequence:**
```
Pre Job Hook → /opt/container-hooks/index.js → /usr/local/bin/docker → exit 1 → Worker exit 102
```

---

### Root Causes — Priority Order

#### CAUSE-01 — Missing GHCR Authentication *(Priority: HIGH)*

The container hook calls `docker create ghcr.io/abenevaut/vapor-ci:php84`. The host Docker daemon tries to pull the image. If the GHCR image is private, the host daemon has no credentials → `docker exit 1`.

**Solution**: Mount the host's `~/.docker/config.json` inside the runner container, OR configure `DOCKER_CONFIG` with a GHCR token. The `ACTIONS_RUNNER_CONTAINER_NETWORK=host` env var does **not** solve auth.

**Architectural decision**: see **DA-08** below.

---

#### CAUSE-02 — Bind-Mount Path Mismatch in the Hook *(Priority: HIGH)*

When the hook calls `docker create -v /home/docker/actions-runner/_work:/github/workspace ...`, the HOST daemon receives this request and looks for `/home/docker/actions-runner/_work` on the HOST filesystem. If that path does not yet exist (first run or cleaned by runner), `docker create` fails with exit 1.

The `docker-wrapper.sh` is supposed to pre-create those directories on `create`/`start`, but container-hooks v0.8.1 may pass volumes differently (e.g., via `--mount` instead of `-v`, or non-standard flags).

**Solution**: Pre-create standard dirs (`_work`, `_work/_temp`, `_work/_actions`, `_work/_tool_cache`) in `entrypoint.sh` Phase 2 (just before `./run.sh`) **AND** ensure `docker-wrapper.sh` handles all volume formats used by container-hooks v0.8.1.

**Risk updated**: see **RISK-01** (new sub-case below).

---

#### CAUSE-03 — Container Hooks v0.8.1 / Runner v2.334.0 Incompatibility *(Priority: MEDIUM)*

Runner v2.334.0 is very recent. The container hooks version (v0.8.1) must be compatible. The official compatibility matrix of `actions/runner-container-hooks` must be checked.

**Action**: Update to the latest verified version of container hooks, pinned with SHA256.

**Risk**: see **RISK-07** below.

---

#### CAUSE-04 — `ACTIONS_RUNNER_CONTAINER_NETWORK=host` Side-Effects *(Priority: LOW)*

`--network host` can cause issues if the job container has specific networking requirements. Less likely as the primary cause, but worth monitoring.

---

### Architectural Decision DA-08 — Docker Credential Forwarding

| Attribute | Detail |
|-----------|--------|
| **Context** | The host Docker daemon must pull GHCR (or other registry) images for `container:` jobs. The daemon uses credentials from the Docker config file. The runner container does not automatically inherit host credentials. |
| **Decision** | Operators MUST provide Docker credentials using **one of the two following mechanisms**: |
| **Mechanism A (recommended)** | Mount the host `~/.docker/config.json` as read-only into the container: `-v ${HOME}/.docker:/home/docker/.docker:ro`. Exposed via optional env var `DOCKER_CONFIG_HOST` (host path → mounted at `/home/docker/.docker`). |
| **Mechanism B (alternative)** | Set env var `GHCR_TOKEN` → `entrypoint.sh` Phase 1 runs `docker login ghcr.io -u <user> --password-stdin`. Less portable (registry-specific). |
| **If neither is set** | Jobs using private images will fail at the "Pre Job Hook" stage with exit code 1. The error message in the runner logs will point to a credential issue. |
| **Public images** | No action required. The host daemon can pull public images from any registry without credentials. |

**New variables** (see `contracts/interface.md`):
- `DOCKER_CONFIG_HOST` (optional, host path to a Docker config directory containing `config.json`) → mounted at `/home/docker/.docker:ro`
- `GHCR_TOKEN` (optional, token for `docker login ghcr.io` during Phase 1)

**`docker-compose.yml` addition** (conditional optional volume):
```yaml
volumes:
  - /var/run/docker.sock:/var/run/docker.sock
  - ${RUNNER_DIR:-/home/docker/actions-runner}:${RUNNER_DIR:-/home/docker/actions-runner}
  # Optional: forward host Docker credentials for pulling private images
  # - ${DOCKER_CONFIG_HOST:-~/.docker}:/home/docker/.docker:ro
```

---

## Phase 0: NEEDS CLARIFICATION — Resolved

All three markers from the spec are resolved in [`research.md`](./research.md):

| Marker | Decision |
|--------|----------|
| `EPHEMERAL` default (FR-050) | `true` — security-first, matches GitHub recommendation |
| Runner auto-update (FR-063) | Out of scope — `--disableupdate`, image rebuild for version bumps |
| Network retry strategy (Edge Cases) | 3 × 10 s via `curl --retry 3 --retry-delay 10`; Docker Compose `restart: always` handles long-term retries |

---

## Phase 1: Architecture & Design

### 1.1 Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          HOST (Ubuntu / Synology)                           │
│                                                                             │
│  /var/run/docker.sock  ◄────── mounted 1:1 into container                  │
│  ${RUNNER_DIR}/        ◄────── mounted 1:1 into container (SAME abs path)  │
│    ├── run.sh          │                                                    │
│    ├── config.sh       │                                                    │
│    ├── .credentials    │                                                    │
│    ├── .runner         │                                                    │
│    └── _work/          │  ◄── job workspaces (bind-mounted by DooD hooks)  │
│                        │                                                    │
│  Docker Daemon ◄───────┤  socket provides access for DooD                  │
│    ├── gh-runner       │  ◄── the runner container itself                  │
│    └── job containers ─┤  ◄── spawned by container hooks via DooD          │
└────────────────────────┘
         ▲
         │ outbound HTTPS
         ▼
   api.github.com / github.com
```

**Single-stage Dockerfile** (no multi-stage needed): the build installs tools directly into the final image. Multi-stage would only save space if we had large build-time-only dependencies, which we don't — the runner binary is downloaded via `curl` and used directly.

---

### 1.2 Component Descriptions

#### `Dockerfile`
Single-stage build on `ubuntu:24.04`. Responsibilities:
- Install OS packages: `ca-certificates curl gnupg jq git build-essential libssl-dev libffi-dev python3 python3-venv python3-dev python3-pip sudo nodejs npm unzip`
- Install Docker CLI only (not the daemon): `docker-ce-cli docker-buildx-plugin docker-compose-plugin`
- Create non-root user `docker` (UID 1000), configure sudoers for env passthrough
- Download `actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz` into `/opt/runner-dist/` + SHA256 verify
- Run `/opt/runner-dist/bin/installdependencies.sh` (runner's own deps)
- Download container hooks zip into `/opt/container-hooks/`
- `COPY entrypoint.sh /entrypoint.sh`
- `COPY docker-wrapper.sh /usr/local/bin/docker` (intercepts `docker run/start` for bind-mount pre-creation)
- Strip CRLF, `chmod +x` all scripts
- `USER root` / `WORKDIR /` → `ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]`

**Key ARGs** (all must be updated together when bumping versions):
```dockerfile
ARG RUNNER_VERSION="2.334.0"
ARG RUNNER_SHA256="048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271"
ARG CONTAINER_HOOKS_VERSION="0.8.1"
```

---

#### `entrypoint.sh`
Two-phase single-script entry point.

**Phase 1 (root — `id -u == 0`)**:
1. Echo `RUNNER_DIR`
2. **Gate 1**: Check `/var/run/docker.sock` is a socket → exit 1 with message if absent
3. **Gate 2**: Self-inspect via `docker inspect` → compare `HostConfig.Mounts.Source` for `RUNNER_DIR` destination → exit 1 with both paths + fix example if mismatch
4. **Stage runner**: if `${RUNNER_DIR}/run.sh` absent → `cp -a /opt/runner-dist/. ${RUNNER_DIR}/` + `chown -R docker:docker`
5. **Permissions**: `mkdir -p ${RUNNER_DIR}/_work` + chown; verify writable
6. **Socket GID fix**: `stat -c '%g' /var/run/docker.sock` → `groupadd --gid <GID> dockerhost` (if not exists) → `usermod -aG <GID> docker`
7. `exec sudo -u docker -E /bin/bash "$0" "$@"`

**Phase 2 (docker user)**:
1. `set -uo pipefail`
2. **Required env validation**: `: "${ACCESS_TOKEN:?...}"` `: "${ORGANIZATION:?...}"`
3. **Placeholder detection**: if `ACCESS_TOKEN == "<YOUR-GITHUB-ACCESS-TOKEN>"` → exit 1
4. Set defaults: `RUNNER_NAME`, `RUNNER_LABELS`, `RUNNER_WORKDIR`, `EPHEMERAL`
5. Export `ACTIONS_RUNNER_CONTAINER_HOOKS=/opt/container-hooks/index.js`
6. Export `ACTIONS_RUNNER_CONTAINER_NETWORK` (default `host`)
7. Derive `API_URL` and `RUNNER_URL` from `ORGANIZATION` + `REPOSITORY`
8. **Token fetch**: `curl --retry 3 --retry-delay 10 --retry-connrefused -fsSX POST ... | jq -r .token`
9. Validate token (non-empty, not `"null"`) → exit 1 with message
10. Build `CONFIG_ARGS` array (add `--ephemeral` if `EPHEMERAL=true`, always add `--disableupdate`)
11. `cd ${RUNNER_DIR}` → `./config.sh "${CONFIG_ARGS[@]}"`
12. `cleanup()` function: `./config.sh remove --token "${REG_TOKEN}"`
13. `trap 'cleanup; exit 130' INT` + `trap 'cleanup; exit 143' TERM`
14. `./run.sh & wait $!`

---

#### `docker-wrapper.sh`
Installed as `/usr/local/bin/docker` (takes priority over `/usr/bin/docker` via PATH). Intercepts `docker start`, `docker run`, `docker create` calls from container hooks to pre-create bind-mount source directories before the host daemon tries to mount them.

**Mechanism**:
- `docker start <id>`: inspect container's `HostConfig.Binds`, `mkdir -p` each source
- `docker run/create [flags]`: parse `-v`/`--volume`/`--mount` args, `mkdir -p` each absolute source
- All other subcommands: pass through to `/usr/bin/docker`
- Falls back to `sudo mkdir -p` if the `docker` user can't write the path directly

This fixes: `"Bind mount failed: '/path/_work/_temp/_github_home' does not exist"` — caused by the runner cleaning `_work/_temp` between jobs.

---

#### `docker-compose.yml`
```yaml
services:
  gh-runner:
    image: ghcr.io/abenevaut/self-hosted-github-runner:latest
    container_name: gh-runner
    restart: always
    platform: linux/amd64
    environment:
      - ORGANIZATION=${ORGANIZATION:-<YOUR-GITHUB-ORGANIZATION>}
      - ACCESS_TOKEN=${ACCESS_TOKEN:-<YOUR-GITHUB-ACCESS-TOKEN>}
      - RUNNER_DIR=${RUNNER_DIR:-/home/docker/actions-runner}
      - REPOSITORY=${REPOSITORY:-}
      - RUNNER_NAME=${RUNNER_NAME:-}
      - RUNNER_LABELS=${RUNNER_LABELS:-self-hosted,linux,x64}
      - RUNNER_WORKDIR=${RUNNER_WORKDIR:-_work}
      - EPHEMERAL=${EPHEMERAL:-true}
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - ${RUNNER_DIR:-/home/docker/actions-runner}:${RUNNER_DIR:-/home/docker/actions-runner}
```

Key properties:
- `restart: always` → automatic re-registration after ephemeral job completion or transient failures
- `platform: linux/amd64` → explicit arch guard (Docker will warn/error on non-amd64 hosts)
- No port mappings (outbound-only communication)
- No `privileged: true` (DooD via socket, not DinD)

---

#### `.env.example`
Provides all variables with status (required/optional), default values, and comments. Placeholder values (`<YOUR-...>`) are intentionally non-functional and detected at startup.

---

### 1.3 DooD Path Mismatch Resolution (Synology)

**Root Cause**: When a workflow uses `container:` or `services:`, the container hooks call `docker run -v ${RUNNER_DIR}/_work:/__w ...`. The host Docker daemon resolves `${RUNNER_DIR}/_work` **on the host filesystem**, not inside the runner container. If the host path differs from the container path, the host daemon fails: `"Bind mount failed: '...' does not exist"`.

**This is not a bug** — it is a fundamental property of DooD (Docker-outside-of-Docker). The host daemon has no visibility into container-internal paths; it only understands host filesystem paths. The path identity constraint (`host_path == container_path`) is the only correct solution.

**Solution** (three-layer defense):

| Layer | Mechanism | Prevents |
|-------|-----------|---------|
| L1: Documentation | `quickstart.md` explains the DooD path identity mechanism with ASCII diagram + comparison table. `docker-compose.yml` comments show ❌ wrong vs ✅ correct patterns and explain **why** both paths must be identical (daemon resolves source on host FS, not inside the runner container). | Operator misconfiguration (most common failure mode) |
| L2: Runtime detection | `docker inspect` self at startup (Gate 2 in entrypoint.sh) → fast-fail with exact paths + fix example | Silent zombie runner registration |
| L3: Pre-creation | `docker-wrapper.sh` intercepts `docker start/run` and `mkdir -p` missing bind sources | Transient `_work` cleanup issues |

**Synology-specific note**: Synology btrfs filesystems report `/proc/self/mountinfo` field 4 as the btrfs subvolume internal path (e.g., `/@docker/volumes/...`), not the actual host path. Only `docker inspect` (querying the host daemon) returns the canonical host path. This is why `docker inspect` is used instead of `/proc/self/mountinfo` parsing.

---

### 1.4 Signal Handling & Graceful Shutdown

```bash
cleanup() {
    echo "[runner] Removing runner..."
    ./config.sh remove --token "${REG_TOKEN}" || true
}
trap 'cleanup; exit 130' INT   # SIGINT  (Ctrl+C)
trap 'cleanup; exit 143' TERM  # SIGTERM (docker compose down)
```

**SIGTERM flow** (`docker compose down`):
1. Docker sends SIGTERM to PID 1 (entrypoint.sh)
2. `trap` fires → `cleanup()` → `config.sh remove --token <REG_TOKEN>`
3. GitHub API call removes the runner from the org/repo
4. Container exits with code 143
5. Runner is no longer visible in GitHub UI (no zombie)

**SIGKILL / OOM**: Runner becomes orphan in GitHub UI (Offline state). Out of scope per spec. Operator must clean via GitHub API or `--replace` on next start (already included via `--replace` in `CONFIG_ARGS`).

**In-flight job on SIGTERM**: `./run.sh` is run as a background job (`& wait $!`). When SIGTERM arrives, the `wait` is interrupted, `cleanup()` runs. If the runner is currently executing a job, `config.sh remove` will wait for the job to complete (or fail it) before deregistering — this is default GitHub Actions runner behavior.

---

### 1.5 Idempotent Startup

| Scenario | Behavior |
|----------|----------|
| First run (empty `RUNNER_DIR`) | `cp -a /opt/runner-dist/. ${RUNNER_DIR}/` → full staging → registration |
| Re-run (staging already done) | Skip cp (detected via `run.sh` presence) → re-registration with `--replace` |
| Re-run with existing `.credentials` | `--replace` overwrites the previous registration without leaving a zombie |
| Binary update (new image) | Re-staging is skipped (presence check on `run.sh`) — but `./bin/installdependencies.sh` already ran at build time. If runner version changes, `RUNNER_DIR` should be cleaned before restart or a new `RUNNER_DIR` used. |

> **Note**: When the image is rebuilt with a new `RUNNER_VERSION`, operators should either use a fresh `RUNNER_DIR` or delete `${RUNNER_DIR}` contents (excluding `_work`) before restarting. The `--replace` flag handles GitHub-side deregistration but not binary version mismatch on disk.

---

## Phase 2: Implementation Sequence

Tasks are ordered to avoid blockers. Each task has a dependency list.

### Task Sequence

```
T-01  Verify Dockerfile is correct (ARGs, SHA256, layer order)
  └── Dependency: none
  └── Deliverable: Dockerfile (no regression)
  └── Acceptance: `docker build --platform linux/amd64 -t test .` succeeds, image < 1.5 GB

T-02  Implement placeholder detection in entrypoint.sh (FR-022)
  └── Dependency: T-01
  └── Changes: entrypoint.sh Phase 2 — add check for `<YOUR-...>` values
  └── Acceptance: container started with placeholder token exits 1 with clear message

T-03  Add --disableupdate to config.sh args in entrypoint.sh (research.md §2)
  └── Dependency: T-01
  └── Changes: entrypoint.sh — add `--disableupdate` to CONFIG_ARGS
  └── Acceptance: config.sh called with `--disableupdate` flag (verify via log)

T-04  Add curl retry flags for token acquisition (research.md §3)
  └── Dependency: T-01
  └── Changes: entrypoint.sh Phase 2 — replace bare curl with `--retry 3 --retry-delay 10 --retry-connrefused`
  └── Acceptance: simulated network failure for 20 s → runner recovers on 3rd attempt

T-05  Verify docker-wrapper.sh handles docker create (not just run/start)
  └── Dependency: T-01
  └── Changes: docker-wrapper.sh — confirm `create` is handled in case statement (L82: already `run|create`)
  └── Acceptance: workflow with `container:` completes without bind-mount errors

T-06  Verify sudoers env_keep list is complete
  └── Dependency: T-01
  └── Changes: Dockerfile — ensure ACTIONS_RUNNER_CONTAINER_HOOKS and ACTIONS_RUNNER_CONTAINER_NETWORK are in Defaults env_keep
  └── Acceptance: `sudo -u docker printenv ACTIONS_RUNNER_CONTAINER_HOOKS` returns the value set in Phase 1

T-07  Add Docker healthcheck to docker-compose.yml (FR-013 observability)
  └── Dependency: T-01
  └── Changes: docker-compose.yml — add healthcheck using `docker inspect` or a sentinel file written by run.sh
  └── Acceptance: `docker compose ps` shows `(healthy)` after runner registers

T-08  Update .env.example (FR-023)
  └── Dependency: T-02, T-03
  └── Changes: .env.example — verify ACTIONS_RUNNER_CONTAINER_NETWORK is documented (commented)
  └── Acceptance: all variables in entrypoint.sh appear in .env.example with description

T-09  Write GitHub Actions CI workflow for image build & publish (FR-060, FR-061)
  └── Dependency: T-01
  └── Deliverable: .github/workflows/publish.yml
  └── Acceptance: workflow_dispatch triggers build for linux/amd64 + push to GHCR with 3 tags

T-10  Write GitHub Actions CI workflow for smoke test (SC-001..SC-006)
  └── Dependency: T-09
  └── Deliverable: .github/workflows/smoke-test.yml (or integrate into T-09)
  └── Acceptance: CI verifies container starts, registers, accepts an echo job, and deregisters on down

T-11  Update README.md (SC-009)
  └── Dependency: T-04..T-08
  └── Changes: README — two sections: Ubuntu standard + Synology DSM, copy-paste examples, security warnings
  └── Acceptance: a new operator can pass SC-001 (< 5 min from clone to Idle) using only the README

T-12  Version bump procedure documentation
   └── Dependency: T-11
   └── Changes: README or CONTRIBUTING.md — document how to update RUNNER_VERSION + SHA256
   └── Acceptance: any contributor can bump the runner version in < 10 min following the doc

T-13  Validate Docker credential forwarding for private GHCR images (DA-08 / CAUSE-01)
   └── Dependency: T-05, T-06
   └── Changes:
         entrypoint.sh — add optional Phase 1 step: if GHCR_TOKEN set → docker login ghcr.io
         docker-compose.yml — add commented volume for DOCKER_CONFIG_HOST mount
         .env.example — document DOCKER_CONFIG_HOST and GHCR_TOKEN (optional)
   └── Acceptance:
         (a) workflow with `container: ghcr.io/abenevaut/vapor-ci:php84` (private) succeeds
             when DOCKER_CONFIG_HOST or GHCR_TOKEN is provided
         (b) same workflow with no credentials → Pre Job Hook fails with exit 1
             and runner log clearly mentions a credential/pull error
         (c) workflow with a public image → succeeds without any credential config

T-14  Verify and extend docker-wrapper.sh for all container-hooks v0.8.1 volume formats (CAUSE-02)
   └── Dependency: T-05
   └── Changes: docker-wrapper.sh — ensure --mount type=bind,source=...,target=... is parsed
         and mkdir -p'd in addition to -v/--volume flag; add debug logging option
   └── Acceptance: workflow with `container:` succeeds even when _work/_temp is absent before job

T-15  Check container-hooks / runner version compatibility matrix (CAUSE-03 / RISK-07)
   └── Dependency: none
   └── Changes: Dockerfile — review CONTAINER_HOOKS_VERSION; update to latest version
         verified against Runner v2.334.0; update SHA256 accordingly
   └── Acceptance: Worker exit code 102 no longer occurs on `container:` jobs after update
```

---

## Interface Contracts Summary

See full contract: [`contracts/interface.md`](./contracts/interface.md)

### Variables (condensed)

| Variable | Required | Default |
|----------|----------|---------|
| `ORGANIZATION` | ✅ | — |
| `ACCESS_TOKEN` | ✅ | — |
| `RUNNER_DIR` | ⬜ | `/home/docker/actions-runner` |
| `REPOSITORY` | ⬜ | _(empty → org-level)_ |
| `RUNNER_NAME` | ⬜ | `$(hostname)` |
| `RUNNER_LABELS` | ⬜ | `self-hosted,linux,x64` |
| `RUNNER_WORKDIR` | ⬜ | `_work` |
| `EPHEMERAL` | ⬜ | `true` |
| `ACTIONS_RUNNER_CONTAINER_NETWORK` | ⬜ | `host` |

### Volumes (both required)

```
/var/run/docker.sock:/var/run/docker.sock
${RUNNER_DIR}:${RUNNER_DIR}   ← SAME path on both sides
```

### Ports: NONE (outbound HTTPS only)

---

## Risks & Mitigations

### RISK-01: DooD Path Mismatch on Synology

| Attribute | Detail |
|-----------|--------|
| **Nature** | **This is not a bug** — it is a structural property of DooD. The host Docker daemon resolves bind-mount sources on the host filesystem; it cannot see inside container A. Therefore `host_path == container_path` is a hard requirement, not a convention. |
| **Likelihood** | High — operators accustomed to "nice" container paths may not realise the constraint, especially on Synology where `/volume1/...` looks unusual |
| **Impact** | P1 — workflows with `container:`/`services:` silently fail with cryptic bind-mount error |
| **Mitigation 1 (L1)** | Documentation: `quickstart.md` section "⚠️ DooD Path Requirement" explains the 3-step mechanism, includes ASCII diagram, and provides a comparison table Ubuntu vs Synology |
| **Mitigation 2 (L2)** | `docker inspect` self-check at startup (Gate 2) → fast-fail with the detected host path, the container path, and a corrected volume mount example |
| **Mitigation 3 (L3)** | `docker-wrapper.sh` pre-creates missing bind-mount dirs before each `docker start/run` (covers transient `_work` cleanup between jobs) |
| **Residual risk** | If the runner container cannot access the Docker socket (Gate 1 fails before Gate 2), the path check is skipped — but Gate 1 produces its own fast-fail |

**Sub-case (CAUSE-02 from diagnostic)**: Even when the host/container path are identical, `_work`, `_work/_temp`, `_work/_actions`, `_work/_tool_cache` may be absent on the host when the first `container:` job runs (directory created by the runner only after a first non-container job, or cleaned between runs). The `docker-wrapper.sh` intercepts `docker create`/`docker run` and pre-creates those paths — **but only if it correctly parses all volume flag formats emitted by container-hooks v0.8.1** (`-v`, `--volume`, `--mount`). If container-hooks changes its flag format, `docker-wrapper.sh` stops being effective.

Additional mitigation (entrypoint.sh Phase 2): pre-create the standard work dirs unconditionally just before `./run.sh`:
```bash
mkdir -p "${RUNNER_DIR}/_work" \
         "${RUNNER_DIR}/_work/_temp" \
         "${RUNNER_DIR}/_work/_actions" \
         "${RUNNER_DIR}/_work/_tool_cache"
chown -R docker:docker "${RUNNER_DIR}/_work"
```
This ensures the host daemon finds those directories even if `docker-wrapper.sh` misses a format variant.

---

### RISK-02: Docker Socket GID Mismatch

| Attribute | Detail |
|-----------|--------|
| **Likelihood** | Medium — GID varies by host OS (999 Debian, 998 Ubuntu server, 987 Synology DSM) |
| **Impact** | P1 — `docker` user cannot call Docker CLI → no DooD → all container jobs fail |
| **Mitigation** | Dynamic GID detection at startup: `stat -c '%g' /var/run/docker.sock` → `groupadd` (idempotent) → `usermod -aG` |
| **Residual risk** | If `/var/run/docker.sock` is absent when Phase 1 runs the GID check, the `stat` silently fails. Ordered after Gate 1 (socket presence check) to avoid this. |

---

### RISK-03: Runner Binary Version Drift

| Attribute | Detail |
|-----------|--------|
| **Likelihood** | Medium — GitHub enforces minimum runner version with ~30-day grace period |
| **Impact** | Medium — runner refuses to accept jobs if it's too old |
| **Mitigation** | `--disableupdate` prevents silent in-place binary updates. Operators must rebuild/repull the image. Monthly image release cadence recommended. |
| **Residual risk** | If maintainers don't publish updated images within GitHub's grace period, deployed runners become non-functional. Mitigated by a CI job that checks for new runner releases. |

---

### RISK-04: Token Expiry After Registration Success

| Attribute | Detail |
|-----------|--------|
| **Likelihood** | Low (registration token valid for 1 hour; used within seconds) |
| **Impact** | Low — `config.sh remove` on SIGTERM uses the short-lived `REG_TOKEN`. If it's expired, deregistration fails silently (protected by `|| true`). |
| **Mitigation** | `REG_TOKEN` is only used at startup (config) and at shutdown (remove). Both operations complete well within the 1-hour validity window in normal operation. Ephemeral runners (one job, then restart) re-acquire a fresh token on each container start. |
| **Residual risk** | Long-lived persistent runner (`EPHEMERAL=false`) running for > 1 hour: SIGTERM → `config.sh remove` with expired token → deregistration fails → zombie in GitHub UI. Documented limitation; recommend `EPHEMERAL=true` to avoid. |

---

### RISK-05: SIGKILL / OOM Leaves Zombie Runner

| Attribute | Detail |
|-----------|--------|
| **Likelihood** | Low-Medium on resource-constrained hosts (Synology, 8 GB RAM) |
| **Impact** | Medium — orphan runner in GitHub UI (`Offline` state) |
| **Mitigation** | `--replace` flag in `config.sh` overwrites any existing registration with the same name on next startup. Orphan gets replaced, not stacked. |
| **Residual risk** | If the runner name changes between restarts, the old orphan persists. Operators must manually delete via GitHub API or UI. |

---

### RISK-06: Synology Container Manager `restart: always` + EPHEMERAL=true Loop Rate

| Attribute | Detail |
|-----------|--------|
| **Likelihood** | Medium — after each ephemeral job, the container exits (code 0) → Docker restarts it → 1 API call per restart |
| **Impact** | Low — GitHub API has generous rate limits for registration; 1 call/restart is negligible. Synology doesn't throttle container restarts by default. |
| **Mitigation** | None required. Documented behavior: ephemeral mode means the container exits after each job. `restart: always` is the correct orchestration policy. |

---

### RISK-07: Container Hook / Runner Version Compatibility

| Attribute | Detail |
|-----------|--------|
| **Likelihood** | Medium — `actions/runner-container-hooks` releases do not always maintain backward compatibility with older hook API versions expected by a given runner release |
| **Impact** | High — incompatibility produces Worker exit code 102 (Pre Job Hook failure) on every `container:` job, making the runner unusable for the main use case |
| **Context** | Confirmed in diagnostic: Runner v2.334.0 + container-hooks v0.8.1 → Worker exit 102 on image `ghcr.io/abenevaut/vapor-ci:php84`. Root cause may be partially or fully a version incompatibility. |
| **Mitigation** | 1. Check `actions/runner-container-hooks` release notes and compatibility matrix against Runner v2.334.0. 2. Pin to the newest hooks version **verified** against that runner version. 3. Express the pin as `CONTAINER_HOOKS_VERSION` ARG in the Dockerfile with SHA256 (already in place). 4. Add an integration test task (T-13, see below) that runs a `container:` job with a private GHCR image end-to-end before any release. |
| **Residual risk** | If GitHub silently changes the hook API contract without a version bump, incompatibility may be discovered only at runtime. Mitigated by the integration test gate (T-13). |

---

## Files Impacted

| File | Action | Description |
|------|--------|-------------|
| `Dockerfile` | **Modify** | Add `--disableupdate` awareness; verify ARG order and SHA256 check |
| `entrypoint.sh` | **Modify** | Add placeholder detection (T-02), `--disableupdate` in CONFIG_ARGS (T-03), curl retry (T-04), pre-create `_work` subdirs before `./run.sh` (CAUSE-02 / RISK-01 sub-case), optional `docker login ghcr.io` via `GHCR_TOKEN` (DA-08) |
| `docker-wrapper.sh` | **Modify** | Extend to handle all volume flag formats used by container-hooks v0.8.1 (`-v`, `--volume`, `--mount`); confirm `create` subcommand handled (CAUSE-02) |
| `docker-compose.yml` | **Modify** | Add healthcheck (T-07); verify all env vars are listed; add commented optional Docker credentials volume mount (DA-08) |
| `.env.example` | **Modify** | Add `ACTIONS_RUNNER_CONTAINER_NETWORK` (commented); add `DOCKER_CONFIG_HOST` and `GHCR_TOKEN` (commented, optional, DA-08); verify completeness |
| `.github/workflows/publish.yml` | **Create** | CI: build linux/amd64 image → push to GHCR with 3 tags (T-09) |
| `.github/workflows/smoke-test.yml` | **Create** | CI: integration smoke test (T-10) |
| `README.md` | **Update** | Two-target quickstart (Ubuntu + Synology), security warnings (T-11); add section on GHCR private image auth (DA-08) |
| `CONTRIBUTING.md` (optional) | **Create** | Runner version bump procedure (T-12); container hooks version validation |

---

## Success Gates

Implementation is complete when all spec Success Criteria pass:

| SC | Criterion | Verification Method |
|----|-----------|-------------------|
| SC-001 | Clone → Idle < 5 min on Ubuntu 24.04 amd64 | Manual test on fresh VM |
| SC-002 | Clone → Idle < 10 min on Synology DSM Intel | Manual test on Synology |
| SC-003 | 100% of invalid configs fail at startup with explicit error | Automated: start container with each invalid config |
| SC-004 | Workflow with `container:` + `services:` succeeds unchanged | GitHub Actions test workflow |
| SC-005 | SIGTERM deregisters within 30 s | `time docker compose down` + GitHub UI check |
| SC-006 | 0 zombies after 10× up/Idle/down cycles | Scripted test (bash loop) |
| SC-007 | Image < 1.5 GB compressed; startup < 30 s (warmed) | `docker image ls` + `time docker compose up` |
| SC-008 | 0 bind-mount failures on 5 reference `container:` workflows | GitHub Actions reference suite |
| SC-009 | README copy-paste examples work for both targets | Validated by SC-001 + SC-002 reviewers |

