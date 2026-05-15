# Tasks — Feature 001: Self-Hosted GitHub Actions Runner

**Feature Branch**: `001-self-hosted-runner`  
**Spec**: [`spec.md`](./spec.md)  
**Plan**: [`plan.md`](./plan.md)  
**Research**: [`research.md`](./research.md)  
**Generated**: 2026-05-14  
**Status**: Implementation complete

---

## Overview

| Metric | Value |
|--------|-------|
| Total tasks | 45 |
| Phase 1 — Setup (Dockerfile) | 8 tasks |
| Phase 2 — Foundational Scripts | 8 tasks |
| Phase 3 — US1 Runner registration & lifecycle | 7 tasks |
| Phase 4 — US2 DooD container:/services: | 8 tasks |
| Phase 5 — US3 Synology deployment | 3 tasks |
| Phase 6 — US4 Configuration via env vars | 3 tasks |
| Phase 7 — US5 Image publication | 3 tasks |
| Phase 8 — Polish & cross-cutting | 5 tasks |
| Parallelisable tasks (`[P]`) | 22 tasks |

### Plan task → spec task mapping

| Plan task | Spec tasks | Priority |
|-----------|-----------|----------|
| T-01 Dockerfile refactor | T001, T003–T008 | Foundational |
| T-02 Placeholder detection | T017 | US1/US4 |
| T-03 `--disableupdate` in CONFIG_ARGS | T019 (partial) | US1 |
| T-04 `curl --retry` for token | T018 | US1 |
| T-05 docker-wrapper `create` subcommand | T016, T031 | Foundational/US2 |
| T-06 `sudoers env_keep` for hook vars | T025 | US2 |
| T-07 Docker healthcheck | T023 | US1 |
| T-08 `.env.example` update | T036, T037 | US4 |
| T-09 CI publish workflow | T038, T039 | US5 |
| T-10 Smoke tests | T041 | Polish |
| T-11 README update | T042–T045 | Polish |
| T-12 Version bump procedure | T040 | US5 |
| T-13 Auth GHCR — CAUSE-01 ⚡ | T026, T027, T028 | **US2 HIGH** |
| T-14 Pre-create dirs + wrapper formats — CAUSE-02 ⚡ | T029, T030 | **US2 HIGH** |
| T-15 Container-hooks compat — CAUSE-03 | T002, T024 | US2 MEDIUM |

> ⚡ = confirmed bug fix (exit 102 / Worker Pre Job Hook failure)

---

## Dependency Graph

```
T001 (Dockerfile ARGs)
 ├── T002 [P] (hooks compat check — can start in parallel)
 ├── T003 (OS packages)
 │    └── T004 [P] (Docker CLI packages)
 │         └── T005 (docker user + sudoers)
 │              └── T006 (runner binary download + SHA256)
 │                   └── T007 [P] (container hooks download)
 │                        └── T008 (COPY scripts + ENTRYPOINT)
 │                             ├── T009 (entrypoint Gate 1)
 │                             │    └── T010 (Gate 2 path check)
 │                             │         └── T011 (runner staging)
 │                             │              └── T012 (GID resolution)
 │                             │                   └── T013 (exec sudo)
 │                             │                        └── T014 (Phase 2 validation)
 │                             │                             └── T015 (derive URLs)
 │                             │                                  ├── [US1] T017→T021
 │                             │                                  ├── [US2] T024→T031
 │                             │                                  ├── [US3] T032→T034
 │                             │                                  └── [US4] T035→T037
 │                             └── T016 (docker-wrapper base)
 └── T022 [P] (docker-compose.yml — parallel with Dockerfile)
```

### User Story completion order

```
Phase 2 (Foundational) → US1 (P1) → US2 (P1, includes CAUSE-01 + CAUSE-02 fixes)
                       → US3 (P1, reuses Gate 2 + compose)
                       → US4 (P2, reuses entrypoint placeholders)
                       → US5 (P2, independent CI workflow)
                       → Polish
```

---

## Phase 1 — Setup: Dockerfile

> **Goal**: Build a reproducible, pinned `ubuntu:24.04` amd64-only image with the runner binary, container hooks, and Docker CLI. All subsequent phases depend on a correct Dockerfile.
>
> **Independent test**: `docker build --platform linux/amd64 -t gh-runner-test . && docker image inspect gh-runner-test | grep Architecture` → `"amd64"`. Image size < 1.5 GB compressed.

- [x] T001 Declare base image `ubuntu:24.04` with `--platform=linux/amd64` and ARGs `RUNNER_VERSION="2.334.0"`, `RUNNER_SHA256`, `CONTAINER_HOOKS_VERSION` at the top of `Dockerfile`
- [x] T002 [P] Research `actions/runner-container-hooks` compatibility matrix for Runner v2.334.0 (check https://github.com/actions/runner-container-hooks/releases), determine the verified compatible version, and update `CONTAINER_HOOKS_VERSION` ARG in `Dockerfile` with its SHA256
- [x] T003 Install OS packages in `Dockerfile`: `ca-certificates curl gnupg jq git build-essential libssl-dev libffi-dev python3 python3-venv python3-dev python3-pip sudo nodejs npm unzip` via `apt-get install --no-install-recommends` with `apt-get clean && rm -rf /var/lib/apt/lists/*`
- [x] T004 [P] Add Docker's official APT repository and install `docker-ce-cli docker-buildx-plugin docker-compose-plugin` in `Dockerfile` (no daemon — CLI only)
- [x] T005 Create `docker` user with `useradd -m -u 1000 docker` and configure `/etc/sudoers.d/docker` with `NOPASSWD: ALL` and `Defaults env_keep += "ACCESS_TOKEN ORGANIZATION REPOSITORY RUNNER_NAME RUNNER_LABELS RUNNER_DIR RUNNER_WORKDIR EPHEMERAL DOCKER_CONFIG_HOST GHCR_TOKEN GHCR_USER RUNNER_ALLOW_RUNASROOT ACTIONS_RUNNER_CONTAINER_HOOKS ACTIONS_RUNNER_CONTAINER_NETWORK"` in `Dockerfile`
- [x] T006 Download `actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz` to `/opt/runner-dist/`, verify SHA256 with `echo "${RUNNER_SHA256}  actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz" | sha256sum -c`, extract, run `/opt/runner-dist/bin/installdependencies.sh` in `Dockerfile`
- [x] T007 [P] Download `actions/runner-container-hooks v${CONTAINER_HOOKS_VERSION}` zip from GitHub releases to `/opt/container-hooks/`, extract (result: `/opt/container-hooks/index.js` exists), verify with SHA256 in `Dockerfile`
- [x] T008 `COPY entrypoint.sh /entrypoint.sh` and `COPY docker-wrapper.sh /usr/local/bin/docker`, strip CRLF (`sed -i 's/\r//'`), `chmod +x` both, set `USER root`, `WORKDIR /`, `ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]` in `Dockerfile`

---

## Phase 2 — Foundational: entrypoint.sh and docker-wrapper.sh

> **Goal**: Core runtime scripts implementing the two-phase entrypoint (Phase 1 root → Phase 2 docker user) and the DooD docker-wrapper. These scripts are prerequisites for all user stories.
>
> **Independent test**: Start the container without `.env` set → container exits 1 with explicit message about missing `ACCESS_TOKEN`. Confirms Phase 2 env validation and Phase 1→2 transition work.

- [x] T009 Implement `entrypoint.sh` Phase 1 — **Gate 1**: check `[ -S /var/run/docker.sock ]`; if absent, `echo "[runner] FATAL: Docker socket not found at /var/run/docker.sock. Add: -v /var/run/docker.sock:/var/run/docker.sock"` and `exit 1` in `entrypoint.sh`
- [x] T010 Implement `entrypoint.sh` Phase 1 — **Gate 2**: use `docker inspect` on the container's own ID (from `/proc/self/cgroup` or `HOSTNAME`) to read `HostConfig.Mounts[].Source` for the `RUNNER_DIR` destination; compare with `$RUNNER_DIR`; if different, print both paths with fix example (`-v <hostpath>:<hostpath>`) and `exit 1` in `entrypoint.sh`
- [x] T011 Implement `entrypoint.sh` Phase 1 — **Runner staging**: if `${RUNNER_DIR}/run.sh` is absent, `cp -a /opt/runner-dist/. ${RUNNER_DIR}/` and `chown -R docker:docker ${RUNNER_DIR}`; if already present, skip copy (idempotent); then `mkdir -p ${RUNNER_DIR}/_work` and verify writable in `entrypoint.sh`
- [x] T012 Implement `entrypoint.sh` Phase 1 — **Dynamic socket GID**: `DOCKER_GID=$(stat -c '%g' /var/run/docker.sock)`; `groupadd --gid ${DOCKER_GID} dockerhost 2>/dev/null || true`; `usermod -aG ${DOCKER_GID} docker` in `entrypoint.sh`
- [x] T013 Implement `entrypoint.sh` Phase 1 — **User switch**: end Phase 1 block with `exec sudo -u docker -E /bin/bash "$0" "$@"` so the remainder of the script runs as the `docker` user in `entrypoint.sh`
- [x] T014 Implement `entrypoint.sh` Phase 2 — **Required env validation**: `set -euo pipefail`; `: "${ACCESS_TOKEN:?[runner] FATAL: ACCESS_TOKEN is required}"` and `: "${ORGANIZATION:?[runner] FATAL: ORGANIZATION is required}"` in `entrypoint.sh`
- [x] T015 Implement `entrypoint.sh` Phase 2 — **URL derivation**: if `REPOSITORY` is non-empty, set `API_URL="https://api.github.com/repos/${ORGANIZATION}/${REPOSITORY}/actions/runners/registration-token"` and `RUNNER_URL="https://github.com/${ORGANIZATION}/${REPOSITORY}"`; else org-level URLs in `entrypoint.sh`
- [x] T016 Implement `docker-wrapper.sh` — **Base architecture**: detect `$1` subcommand; for `start`, `run`, `create`: parse volume flags and pre-create dirs, then delegate to `/usr/bin/docker "$@"`; for all other subcommands: `exec /usr/bin/docker "$@"` passthrough; add `#!/usr/bin/env bash` + `set -euo pipefail` header in `docker-wrapper.sh`

---

## Phase 3 — User Story 1: Lancer un runner org-level fiable

> **US1 Goal**: As a DevOps operator on Ubuntu amd64, I can `git clone` → fill `.env` → `docker compose up -d` → see runner `Idle` on GitHub within 2 minutes, with clean deregistration on `docker compose down`.
>
> **Independent test (SC-001)**: On a fresh Ubuntu 24.04 amd64 VM with Docker installed, run quickstart with a valid PAT; runner must appear `Idle` at `https://github.com/organizations/<ORG>/settings/actions/runners` and accept a trivial `echo hello` workflow.

- [x] T017 [US1] Add **placeholder detection** in `entrypoint.sh` Phase 2: after required-env validation, check `if [[ "$ACCESS_TOKEN" == "<YOUR-GITHUB-ACCESS-TOKEN>" ]] || [[ "$ORGANIZATION" == "<YOUR-GITHUB-ORGANIZATION>" ]]`; exit 1 with explicit message naming the placeholder value in `entrypoint.sh`
- [x] T018 [P] [US1] Implement **GitHub API token fetch** with retry in `entrypoint.sh` Phase 2: `REG_TOKEN=$(curl --retry 3 --retry-delay 10 --retry-connrefused -fsSL -X POST -H "Authorization: Bearer ${ACCESS_TOKEN}" -H "X-GitHub-Api-Version: 2022-11-28" "${API_URL}" | jq -r .token)`; validate result non-empty and not `"null"` else exit 1 with message in `entrypoint.sh`
- [x] T019 [US1] Build `CONFIG_ARGS` array in `entrypoint.sh` Phase 2 with: `--url "${RUNNER_URL}"`, `--token "${REG_TOKEN}"`, `--name "${RUNNER_NAME:-$(hostname)}"`, `--labels "${RUNNER_LABELS:-self-hosted,linux,x64}"`, `--work "${RUNNER_WORKDIR:-_work}"`, `--replace`, `--disableupdate`; append `--ephemeral` if `EPHEMERAL=true`; then `cd "${RUNNER_DIR}" && ./config.sh "${CONFIG_ARGS[@]}"` in `entrypoint.sh`
- [x] T020 [US1] Launch runner as background job and wait: `./run.sh & wait $!` in `entrypoint.sh` Phase 2 (background + wait is required for trap to fire on SIGTERM) in `entrypoint.sh`
- [x] T021 [US1] Implement `cleanup()` function and signal traps in `entrypoint.sh` Phase 2: `cleanup() { echo "[runner] Removing runner..."; cd "${RUNNER_DIR}"; ./config.sh remove --token "${REG_TOKEN}" || true; }`; `trap 'cleanup; exit 130' INT`; `trap 'cleanup; exit 143' TERM` in `entrypoint.sh`
- [x] T022 [P] [US1] Create `docker-compose.yml` with service `gh-runner`, `image: ghcr.io/abenevaut/self-hosted-github-runner:latest`, `container_name: gh-runner`, `restart: always`, `platform: linux/amd64`, all env vars (`ORGANIZATION`, `ACCESS_TOKEN`, `RUNNER_DIR`, `REPOSITORY`, `RUNNER_NAME`, `RUNNER_LABELS`, `RUNNER_WORKDIR`, `EPHEMERAL`), required volumes (`/var/run/docker.sock` + `${RUNNER_DIR}:${RUNNER_DIR}`) in `docker-compose.yml`
- [x] T023 [P] [US1] Add Docker **healthcheck** to `docker-compose.yml`: use a sentinel file (`touch ${RUNNER_DIR}/.runner-ready` in `entrypoint.sh` Phase 2 after `./config.sh` completes) and `healthcheck: { test: ["CMD", "test", "-f", "${RUNNER_DIR}/.runner-ready"], interval: 10s, timeout: 5s, retries: 12 }` in `docker-compose.yml`

---

## Phase 4 — User Story 2: Exécuter des workflows `container:` / `services:`

> **US2 Goal**: As a developer, I can use `jobs.<id>.container:` and `services:` (e.g. postgres) on this self-hosted runner without workflow changes vs GitHub-hosted runners, with no "Bind mount failed" error and no Pre Job Hook exit code 102.
>
> **Independent test (SC-004, SC-008)**: Trigger a workflow with `jobs.test.container: { image: ubuntu:24.04 }` AND `services.postgres: { image: postgres:16 }`; the job must run without bind-mount error and reach the postgres service by DNS.

> ⚡ **CAUSE-01 fix (HIGH)**: Tasks T026–T028 — Docker registry auth for private images  
> ⚡ **CAUSE-02 fix (HIGH)**: Tasks T029–T030 — Pre-create bind-mount dirs  
> ⚠️  **CAUSE-03 fix (MEDIUM)**: Tasks T002, T024 — Container hooks version compatibility

- [x] T024 [US2] Set **container hooks env vars** in `entrypoint.sh` Phase 2 (before `./config.sh`): `export ACTIONS_RUNNER_CONTAINER_HOOKS=/opt/container-hooks/index.js` and `export ACTIONS_RUNNER_CONTAINER_NETWORK="${ACTIONS_RUNNER_CONTAINER_NETWORK:-bridge}"` in `entrypoint.sh`
- [x] T025 [US2] Verify `Dockerfile` sudoers `Defaults env_keep` includes `ACTIONS_RUNNER_CONTAINER_HOOKS` and `ACTIONS_RUNNER_CONTAINER_NETWORK` (required for `exec sudo -u docker -E` to propagate them); add if missing in `Dockerfile`
- [x] T026 [US2] Implement **GHCR_TOKEN auth** (DA-08 Mechanism B) in `entrypoint.sh` Phase 1 (root, before `exec sudo`): `if [[ -n "${GHCR_TOKEN:-}" ]]; then echo "${GHCR_TOKEN}" | docker login ghcr.io -u "${GHCR_USER:-x-token}" --password-stdin; fi` in `entrypoint.sh`
- [x] T027 [P] [US2] Implement **DOCKER_CONFIG_HOST mount** (DA-08 Mechanism A) validation in `entrypoint.sh` Phase 1: validate that `/home/docker/.docker` is mounted when `DOCKER_CONFIG_HOST` is set; warn if not; configure via docker-compose volume (preferred — see T028) in `entrypoint.sh`
- [x] T028 [P] [US2] Add **commented optional DOCKER_CONFIG_HOST volume** to `docker-compose.yml`: `# - ${DOCKER_CONFIG_HOST:-~/.docker}:/home/docker/.docker:ro` with explanatory comment about DA-08 and when this is required; also add `DOCKER_CONFIG_HOST` and `GHCR_TOKEN` to the `environment:` block in `docker-compose.yml`
- [x] T029 [US2] Pre-create **`_work` subdirectories** in `entrypoint.sh` Phase 1 (after staging) AND Phase 2 (just before `./run.sh`): `mkdir -p "${RUNNER_DIR}/_work" "${RUNNER_DIR}/_work/_temp" "${RUNNER_DIR}/_work/_actions" "${RUNNER_DIR}/_work/_tool_cache" "${RUNNER_DIR}/_work/_PipelineMapping" "${RUNNER_DIR}/_diag"` in `entrypoint.sh`
- [x] T030 [US2] Extend `docker-wrapper.sh` to parse **`--mount type=bind,source=<src>,target=<dst>`** format in addition to `-v`/`--volume` flags: already implemented via `extract_mount_source()` function in `docker-wrapper.sh`
- [x] T031 [P] [US2] Verify `docker-wrapper.sh` handles **`create` subcommand** in the case statement alongside `run` and `start`: already present as `run|create` branch in `docker-wrapper.sh`

---

## Phase 5 — User Story 3: Déploiement sur Synology DSM

> **US3 Goal**: As a Synology NAS operator (amd64), I can deploy via Container Manager with `RUNNER_DIR=/volume1/...` and have `container:` jobs succeed — or get an explicit fast-fail with diagnosis if the path constraint is violated.
>
> **Independent test (SC-002, SC-003)**: On Synology DSM Intel with `RUNNER_DIR=/volume1/docker/actions-runner`, start container → runner registers → `container:` workflow succeeds. With mismatched paths → container exits at startup with DooD path mismatch message.

- [x] T032 [US3] Verify `docker-compose.yml` has `platform: linux/amd64` on the service and that the `${RUNNER_DIR}:${RUNNER_DIR}` volume uses identical paths on both sides; add inline comment explaining the DooD path-identity requirement in `docker-compose.yml`
- [x] T033 [P] [US3] Ensure Gate 2 in `entrypoint.sh` produces the correct diagnostic message on mismatch with `Container RUNNER_DIR` and `Host source path` labels, and exits 1 in `entrypoint.sh`
- [x] T034 [P] [US3] `quickstart.md` already has a **DooD path requirement section** with ASCII diagram, Ubuntu vs Synology comparison table, ❌ wrong vs ✅ correct compose volume examples, and explanation of why host daemon resolves sources on host filesystem

---

## Phase 6 — User Story 4: Configurabilité par variables d'environnement

> **US4 Goal**: As an operator, I can configure all runner parameters via env vars only (no internal config file editing), supporting integration with `.env`, Docker secrets, Synology secrets.
>
> **Independent test**: Start container twice with two different env sets (one org-level with custom labels, one repo-level with different name) without modifying the image — both runners register with correct parameters.

- [x] T035 [US4] Set **default values** for optional variables in `entrypoint.sh` Phase 2: `RUNNER_NAME="${RUNNER_NAME:-$(hostname)}"`, `RUNNER_LABELS="${RUNNER_LABELS:-self-hosted,linux,x64}"`, `RUNNER_WORKDIR="${RUNNER_WORKDIR:-_work}"`, `EPHEMERAL="${EPHEMERAL:-true}"`, `RUNNER_DIR="${RUNNER_DIR:-/home/docker/actions-runner}"` in `entrypoint.sh`
- [x] T036 [P] [US4] Create/update `.env.example` with all **required variables** (`ACCESS_TOKEN`, `ORGANIZATION`) with placeholder values and "REQUIRED" comment; all **optional variables** (`REPOSITORY`, `RUNNER_NAME`, `RUNNER_LABELS`, `RUNNER_WORKDIR`, `EPHEMERAL`, `RUNNER_DIR`) with documented defaults and descriptions in `.env.example`
- [x] T037 [P] [US4] Add **DA-08 optional variables** as commented blocks in `.env.example`: `DOCKER_CONFIG_HOST`, `GHCR_TOKEN`, `GHCR_USER`, and `ACTIONS_RUNNER_CONTAINER_NETWORK` with descriptions in `.env.example`

---

## Phase 7 — User Story 5: Image publiée et reproductible

> **US5 Goal**: As an operator or maintainer, the image is published on GHCR, built only for `linux/amd64`, with traceable versioning (runner version + date tag) to pin in production.
>
> **Independent test**: `docker pull ghcr.io/abenevaut/self-hosted-github-runner:<tag>` on amd64 succeeds; `docker image inspect` shows `Architecture: amd64` only; versioned + latest tags coexist.

- [ ] T038 [US5] **SKIPPED** — CI/CD file creation deferred to infrastructure team. Create `.github/workflows/publish.yml`: trigger on `workflow_dispatch` + push to `main`; jobs: `build-and-push`; authenticate with `GITHUB_TOKEN` to GHCR; build `--platform linux/amd64` only; push to `ghcr.io/abenevaut/self-hosted-github-runner` in `.github/workflows/publish.yml`
- [ ] T039 [P] [US5] **SKIPPED** — depends on T038. Configure **3-tag strategy** in `publish.yml`
- [x] T040 [P] [US5] Create `CONTRIBUTING.md` with **runner version bump procedure**: steps to check releases, download tarball + compute SHA256, update `RUNNER_VERSION` and `RUNNER_SHA256` in `Dockerfile`, check container-hooks compatibility, open PR → CI builds → review → merge → GHCR push in `CONTRIBUTING.md`

---

## Phase 8 — Polish & Cross-Cutting Concerns

> **Goal**: Smoke tests, README documentation covering both deployment targets, security warnings, and private image auth guide. No new features — validates and documents all previous phases.

- [ ] T041 **SKIPPED** — CI/CD file creation deferred to infrastructure team. Create `.github/workflows/smoke-test.yml`
- [x] T042 [P] Update `README.md` with **Ubuntu quickstart section**: prerequisites (Docker ≥ 20.10, PAT scopes), 5-step guide (clone → `cp .env.example .env` → fill `.env` → `mkdir -p $RUNNER_DIR` → `docker compose up -d`), expected output (runner Idle in GitHub UI), timing target SC-001 (< 5 min from clone to Idle) in `README.md`
- [x] T043 [P] Update `README.md` with **Synology DSM section**: Container Manager setup, mandatory use of `/volume1/...` path for `RUNNER_DIR`, correct volume mount example, explicit DooD path requirement warning with ❌/✅ examples, link to `quickstart.md` for full diagram in `README.md`
- [x] T044 [P] Add **security warnings section** to `README.md` (FR-041): (a) mounting the Docker socket grants effective root access to the host daemon; (b) do not use this runner on public repositories accepting external PRs; (c) `EPHEMERAL=true` default is the recommended mode per GitHub security guidelines in `README.md`
- [x] T045 [P] Add **private image auth section** to `README.md` (DA-08): explain CAUSE-01 (Pre Job Hook exit 102 when GHCR image is private), document Option A (`DOCKER_CONFIG_HOST` volume mount) vs Option B (`GHCR_TOKEN` env var), copy-paste docker-compose snippets for each option, note that public images require no configuration in `README.md`

---

## Quality Checklist

### Implementation Readiness

- [x] All T001–T037, T040, T042–T045 tasks completed and checkboxes ticked
- [ ] `docker build --platform linux/amd64 -t test . && docker image ls test` → image < 1.5 GB
- [ ] Container starts with valid `.env` → runner appears `Idle` in GitHub UI (SC-001 < 5 min)
- [ ] Container starts with invalid token → exits 1 with explicit error message (SC-003)
- [ ] Container starts with placeholder `ACCESS_TOKEN` → exits 1 with placeholder error (SC-003)
- [ ] Container starts without Docker socket → exits 1 with socket error (SC-003)
- [ ] Container starts with mismatched host/container paths → exits 1 with DooD path error (SC-003)
- [ ] `docker compose down` → runner deregistered within 30 s (SC-005)
- [ ] Workflow with `container:` + `services:` succeeds — zero "Bind mount failed" errors (SC-004, SC-008)
- [ ] Worker exit code 102 no longer occurs after CAUSE-01 and CAUSE-02 fixes (T026–T031)
- [ ] 10× up/Idle/down cycles → 0 zombie runners in GitHub UI (SC-006)

### Documentation Readiness

- [x] `.env.example` covers all variables with status, default, description
- [x] `README.md` has Ubuntu quickstart (copy-paste, no extra steps)
- [x] `README.md` has Synology DSM section (correct volume path examples)
- [x] `README.md` has security warnings (socket, no public repos, EPHEMERAL)
- [x] `README.md` has GHCR private image auth guide (DA-08)
- [x] `CONTRIBUTING.md` has runner version bump procedure
- [x] `quickstart.md` has DooD path requirement with ASCII diagram

### CI/CD Readiness

- [ ] `.github/workflows/publish.yml` builds and pushes `linux/amd64` only to GHCR — **DEFERRED**
- [ ] 3 tags produced: `latest`, `YYYY.MM.DD`, `runner-vX.Y.Z` — **DEFERRED**
- [ ] `.github/workflows/smoke-test.yml` validates SC-001 and SC-005 end-to-end — **DEFERRED**

---

## Parallel Execution Examples

### US1 – Runner registration (Phase 3)

Tasks that can run in parallel once Phase 2 is complete:

```
T017 (placeholder detection)     ◄─┐
T018 (curl --retry token)        ◄─┤ All Phase 2 entrypoint
T019 (CONFIG_ARGS + config.sh)   ◄─┤ tasks done → implement
T020 (./run.sh background)       ◄─┤ in one entrypoint.sh pass,
T021 (SIGTERM trap)              ◄─┤ but logically independent
T022 (docker-compose.yml) [P]   ◄─┘ (different file — parallel)
T023 (healthcheck) [P]           ◄── docker-compose.yml (parallel with T022)
```

### US2 – DooD fixes (Phase 4)

```
T024 (hook env vars in entrypoint)    ◄─┐
T025 (sudoers env_keep in Dockerfile) ◄─┤ Different files — fully parallel
T026 (GHCR_TOKEN login entrypoint)   ◄─┤
T027 (DOCKER_CONFIG_HOST in entry)   ◄─┤
T028 (compose optional volume) [P]   ◄─┘
T029 (pre-create _work dirs)          ◄─┐ entrypoint.sh
T030 (docker-wrapper --mount parse)  ◄─┤ docker-wrapper.sh (parallel)
T031 (wrapper create subcommand) [P] ◄─┘
```

### US4, US5, Polish (Phases 6–8)

```
T036 + T037 (.env.example)           ◄─┐
T038 + T039 (publish.yml)            ◄─┤ Fully parallel — different files
T040 (CONTRIBUTING.md)               ◄─┤
T041 (smoke-test.yml)                ◄─┤
T042–T045 (README.md sections)       ◄─┘
```

---

## Implementation Strategy

### MVP scope — US1 alone (ship first)

Implement Phases 1–3 (T001–T023) to deliver a reliable org-level runner that registers, accepts trivial jobs, and deregisters cleanly. Validates the basic infrastructure without the complexity of `container:` / `services:`.

**Suggested order within MVP**:
1. T001 → T003 → T004 → T005 → T006 → T007 → T008 (Dockerfile, sequential)
2. T009 → T010 → T011 → T012 → T013 → T014 → T015 (entrypoint.sh, sequential)
3. T016 (docker-wrapper.sh base, parallel)
4. T017 → T018 → T019 → T020 → T021 (entrypoint.sh US1 additions, sequential)
5. T022 + T023 (docker-compose.yml, parallel)

### Increment 2 — US2 CAUSE-01 + CAUSE-02 fixes (highest priority after MVP)

T024 → T025 → T026 → T029 in sequence (entrypoint edits), T027 → T028 (compose edits, parallel), T030 → T031 (docker-wrapper edits, parallel). **These are the confirmed bug fixes for exit 102.**

### Increment 3 — US2 CAUSE-03 + US3 Synology

T002 (hooks compat check, immediate) → update Dockerfile if version changes → T032 → T033 → T034.

### Increment 4 — US4 Config + US5 CI + Polish

T035–T037 (env vars), T038–T040 (CI), T041–T045 (README + smoke tests). Fully parallelisable across engineers.
