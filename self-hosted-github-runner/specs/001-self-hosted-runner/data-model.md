# Data Model — Feature 001: Self-Hosted GitHub Actions Runner

**Generated**: 2026-03-19  
**Branch**: `001-self-hosted-runner`

---

## Entities

### 1. RunnerContainer

The Docker container that embeds and runs the GitHub Actions runner process.

| Field | Type | Description |
|-------|------|-------------|
| `RUNNER_DIR` | `string (abs path)` | Absolute path identical on host and inside container. Critical DooD constraint. Default: `/home/docker/actions-runner` |
| `RUNNER_NAME` | `string` | Runner name as displayed in GitHub UI. Default: `$(hostname)` |
| `RUNNER_LABELS` | `csv string` | Comma-separated list of routing labels. Default: `self-hosted,linux,x64` |
| `RUNNER_WORKDIR` | `string` | Relative path inside `RUNNER_DIR` for job workspaces. Default: `_work` |
| `EPHEMERAL` | `bool` | If `true`, runner exits after one job. Default: `true` |
| `lifecycle_state` | `enum` | See state transitions below |

**Invariant**: `host_path(RUNNER_DIR) == container_path(RUNNER_DIR)` must hold at startup. Violation → fast-fail with diagnostic.

---

### 2. GitHubRegistrationTarget

The GitHub org or repository the runner registers with.

| Field | Type | Description |
|-------|------|-------------|
| `ORGANIZATION` | `string` | GitHub org or owner name. **Required**. |
| `REPOSITORY` | `string` | Repository name (optional). If set → repo-level runner. |
| `registration_url` | `string` | Derived: `https://github.com/{ORGANIZATION}[/{REPOSITORY}]` |
| `api_token_url` | `string` | Derived: GitHub REST API endpoint for registration-token |

---

### 3. RunnerIdentity

The identity material stored by the runner after registration.

| Field | Type | Description |
|-------|------|-------------|
| `ACCESS_TOKEN` | `string (secret)` | GitHub PAT. **Required**. Never written to volume beyond runner's internal `.credentials`. |
| `registration_token` | `string (ephemeral)` | Short-lived token (1h) obtained from GitHub API. Used once for `config.sh`. |
| `.credentials` file | `file` | Written by `config.sh` into `RUNNER_DIR/`. Contains long-lived runner credentials. |
| `.runner` file | `file` | Written by `config.sh`. Contains runner metadata (id, name, labels). |

---

### 4. HostDockerSocket

The UNIX socket giving access to the host Docker daemon inside the container.

| Field | Type | Description |
|-------|------|-------------|
| `socket_path` | `string` | Always `/var/run/docker.sock` on host and inside container (1:1 mount). |
| `socket_gid` | `int` | GID of the socket on the host. Detected at runtime via `stat -c '%g'`. Variable across hosts (999/998/987). |
| `docker_user_groups` | `[]int` | Supplementary groups for the `docker` user inside the container. Must include `socket_gid`. |

**Invariant**: `/var/run/docker.sock` must be a socket file (`-S`) at startup. Absence → fast-fail.

---

### 5. ContainerHooks

The container hooks package enabling `container:` and `services:` job execution.

| Field | Type | Description |
|-------|------|-------------|
| `install_path` | `string` | `/opt/container-hooks/` — baked into image, not on host volume |
| `entry_point` | `string` | `/opt/container-hooks/index.js` |
| `network_mode` | `string` | `ACTIONS_RUNNER_CONTAINER_NETWORK`. Default: `host`. |
| `version` | `string` | Pinned in Dockerfile `ARG CONTAINER_HOOKS_VERSION` |

---

## State Transitions: RunnerContainer Lifecycle

```
                 ┌──────────────────────────────────────────────────────┐
                 │                  CONTAINER START                      │
                 └──────────────────────────┬───────────────────────────┘
                                            │
                                            ▼
                              ┌─────────────────────────┐
                              │  PHASE 1: init (root)   │
                              │  - check /docker.sock   │
                              │  - check path equality  │
                              │  - stage runner files   │
                              │  - fix socket GID       │
                              └─────────────┬───────────┘
                                            │
                          ┌─────────────────┴──────────────────┐
                          │ validation failure?                  │
                    YES   │                              NO      │
                   ───────┘                              └───────
                   ▼                                            ▼
           ┌──────────────┐                    ┌───────────────────────────┐
           │  FAILED_INIT │                    │  PHASE 2: register        │
           │  exit 1      │                    │  (docker user)            │
           └──────────────┘                    │  - validate env vars      │
                                               │  - GET registration token │
                                               │  - run config.sh          │
                                               └─────────────┬─────────────┘
                                                             │
                                         ┌───────────────────┴───────────────────┐
                                         │ registration failure?                  │
                                   YES   │                                NO      │
                                  ───────┘                                └───────
                                  ▼                                               ▼
                          ┌──────────────┐                          ┌─────────────────────┐
                          │  FAILED_REG  │                          │        IDLE          │
                          │  exit 1      │                          │  (run.sh listening)  │
                          └──────────────┘                          └──────────┬──────────┘
                                                                               │
                                                          ┌────────────────────┴──────────────────────┐
                                                          │  job received                              │
                                                          ▼                                            │
                                                 ┌────────────────┐                                   │
                                                 │  RUNNING_JOB   │                                   │
                                                 └───────┬────────┘                                   │
                                                         │                                            │
                                         ┌───────────────┴───────────────┐                           │
                                         │ EPHEMERAL=true?               │ EPHEMERAL=false            │
                                   YES   │                               └───────────────────────────►│
                                  ───────┘
                                  ▼
                         ┌─────────────────┐
                         │  DRAINING       │
                         │  (run.sh exits) │
                         └──────┬──────────┘
                                │
                                ▼
                         ┌─────────────────┐
                         │  DEREGISTERING  │ ◄── also triggered by SIGTERM/SIGINT
                         │  config.sh      │     at any state
                         │  remove --token │
                         └──────┬──────────┘
                                │
                                ▼
                         ┌─────────────────┐
                         │  STOPPED        │
                         │  exit 0/130/143 │
                         └─────────────────┘
```

---

## Validation Rules

| Validation | Trigger | Failure Mode |
|-----------|---------|-------------|
| `ACCESS_TOKEN` non-empty, not `<YOUR-GITHUB-ACCESS-TOKEN>` | Phase 2 start | `exit 1` + message |
| `ORGANIZATION` non-empty, not `<YOUR-GITHUB-ORGANIZATION>` | Phase 2 start | `exit 1` + message |
| `/var/run/docker.sock` is a socket file | Phase 1 | `exit 1` + message |
| `RUNNER_DIR` host path == container path | Phase 1 | `exit 1` + both paths + fix example |
| `RUNNER_DIR` writable | Phase 1 staging | `exit 1` + message |
| Registration token non-null/non-"null" | Phase 2 after API call | `exit 1` + HTTP hint |
| `config.sh` exits 0 | Phase 2 | `exit 1` |

