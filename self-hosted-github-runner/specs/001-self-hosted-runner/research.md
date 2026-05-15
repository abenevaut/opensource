# Research — Feature 001: Self-Hosted GitHub Actions Runner (DooD, amd64)

**Generated**: 2026-03-19  
**Updated**: 2026-05-14 (diagnostic findings from `_diag/` log analysis added)  
**Branch**: `001-self-hosted-runner`  
**Status**: Complete — all NEEDS CLARIFICATION resolved

---

## Resolution 1 — EPHEMERAL default value (FR-050)

### Decision
`EPHEMERAL=true` is the **default** and **recommended** value.

### Rationale
- GitHub officially recommends ephemeral mode for self-hosted runners in security documentation (ref: [docs.github.com/en/actions/security-for-github-actions/security-guides/security-hardening-for-github-actions#using-just-in-time-runners](https://docs.github.com/en/actions/security-for-github-actions/security-guides/security-hardening-for-github-actions)).
- Ephemeral runners eliminate **cross-job secret leakage** (environment variables, files left in `_work`, shell history).
- In DooD contexts, ephemeral mode prevents Docker image/layer accumulation in the host daemon cache between different workflows.
- The operational cost (container restarts per job) is fully absorbed by the `restart: always` policy in `docker-compose.yml`: after a job, the container exits cleanly, Docker Compose restarts it, and it re-registers for the next job.
- Current implementation already defaults to `true` — confirmed by `entrypoint.sh` L128.
- Persistent mode (`EPHEMERAL=false`) remains available via explicit env var override for operators who accept the trade-off.

### Alternatives Considered
- `EPHEMERAL=false` as default: simpler for initial demos, but violates GitHub's security guidance and creates subtle state pollution bugs. Rejected.
- No default (fail fast if not set): increases friction for ops with no security gain. Rejected.

---

## Resolution 2 — Runner binary auto-update (FR-063)

### Decision
Auto-update of the GitHub Actions runner binary is **explicitly out of scope**. The `--disableupdate` flag is passed to `config.sh` at registration. Runner version bumps are handled by **rebuilding and republishing the Docker image**.

### Rationale
- The runner binary lives on the **host-mounted volume** (`RUNNER_DIR`). If GitHub's built-in auto-updater downloads a new binary into that directory, it modifies the host filesystem outside of Docker's control, creates version drift between the image and the runtime, and can cause SHA256/signature mismatches on next image pull.
- Reproducibility principle: the Docker image is the single source of truth for the runner version (`ARG RUNNER_VERSION` + SHA256 check in Dockerfile). This is in line with how teams pin action versions (`uses: actions/checkout@v4`).
- `--disableupdate` is a supported, stable flag of `actions/runner` since v2.303.0 (well before our baseline of v2.334.0).
- The update procedure (bump `RUNNER_VERSION` + `RUNNER_SHA256` in Dockerfile → CI rebuild → GHCR push) is straightforward and documented in `plan.md`.
- GitHub does not immediately break runners that are slightly behind the minimum version — there is typically a 30-day grace period before forced updates. Regular image releases (monthly or per GitHub advisory) are sufficient.

### Alternatives Considered
- Allow auto-update by omitting `--disableupdate`: creates non-deterministic image state, potentially breaks startup on next container restart if `_work` and `_diag` directories contain stale auto-updated files. Rejected.
- DooD-aware auto-update (patch `run.sh`): too complex, fragile, and out of scope for a clean rewrite. Rejected.

---

## Resolution 3 — Network retry strategy at startup (Edge Cases)

### Decision
Token acquisition uses **3 attempts with fixed 10-second waits** (total exposure: up to 30 seconds). If all 3 fail, the container exits with a clear error. There is **no exponential backoff** loop.

### Rationale
- NAS/home-server boot scenarios (Synology DSM) typically see a ≤ 15-second DNS resolution delay after Docker Compose starts. Three 10-second retries cover this without leaving the container in an invisible pending state for minutes.
- The Docker Compose `restart: always` policy handles long-term transient failures: if registration fails after 30 s, the container exits and Docker Compose retries the full startup, providing a natural retry loop at the orchestration level.
- A 5-minute exponential backoff loop would (a) delay operator feedback in true misconfiguration cases, (b) make the container appear healthy to `docker compose ps` while doing nothing, and (c) complicate log parsing.
- curl's `--retry 3 --retry-delay 10 --retry-connrefused` flags implement this cleanly without a bash loop.

### Alternatives Considered
- Exponential backoff for 5 min: better for flaky networks but dangerously masks misconfig. Rejected (FR-015 mandates "fail fast").
- `--retry-all-errors` + 5 attempts (50 s): considered, but 3 × 10 s is sufficient for the stated use cases.
- Fail immediately on first error: too harsh for boot-time DNS delays. Rejected.

---

## Research Finding 4 — actions/runner Container Hooks (DooD prerequisite)

### Decision
Use the official **`actions/runner-container-hooks`** package (Docker variant) at a pinned version. Current: `v0.8.1`. The hooks binary is installed into `/opt/container-hooks/` (image layer, not the host volume).

### Rationale
- Container hooks are the **official GitHub mechanism** for running `container:` and `services:` jobs on self-hosted runners. Without them, the runner falls back to running jobs directly on the host filesystem, which breaks `container:` directives.
- Placing hooks in the image (not in `RUNNER_DIR`) is critical: the runner container hooks are executed **inside the runner container** by the runner process. They do not need to be visible to the host Docker daemon. Placing them in `RUNNER_DIR` (host filesystem) would work but creates unnecessary coupling.
- `ACTIONS_RUNNER_CONTAINER_HOOKS=/opt/container-hooks/index.js` is set in the environment before `./run.sh` starts.
- `ACTIONS_RUNNER_CONTAINER_NETWORK=host` is the safest default for DooD (services need to reach the runner's localhost). Operators can override to a named network.

### Alternatives Considered
- DinD (Docker-in-Docker): provides full isolation but requires `--privileged`, introduces daemon-in-daemon complexity, and is explicitly out of scope per spec.
- No container hooks (shell-only jobs): eliminates `container:`/`services:` support. Rejected (P1 requirement).

---

## Research Finding 5 — Docker socket GID dynamic resolution

### Decision
At startup (Phase 1, root), detect the GID of `/var/run/docker.sock` via `stat -c '%g'`, create a supplementary group with that GID if it doesn't exist, and add the `docker` user to it. This is already implemented in `entrypoint.sh` (L100-L105).

### Rationale
- The GID of `/var/run/docker.sock` varies by Linux distribution: 999 on Debian-based, 998 or 981 on some RHEL-based, 987 on Synology DSM. Baking a fixed GID into the image would break on different hosts.
- Dynamic GID assignment is the standard pattern used by tools like `docker/setup-qemu-action` and by most community runner images.
- `chmod 666 /var/run/docker.sock` is a security anti-pattern — rejected.

### Alternatives Considered
- Build-time ARG `DOCKER_GID`: works only if operator knows the host GID in advance and rebuilds the image. Not compatible with a pre-built GHCR image. Rejected.
- Run container as root: violates FR-040. Rejected.

---

## Research Finding 6 — DooD path-mismatch detection via `docker inspect`

### Decision
Use `docker inspect` on the runner's own container ID to read `HostConfig.Mounts[].Source` for the `RUNNER_DIR` destination. Compare with `$RUNNER_DIR`. Fail fast if different.

### Rationale
- `/proc/self/mountinfo` field 4 (mount root) does NOT reflect the actual host path on Synology btrfs filesystems — it shows the btrfs subvolume internal path (e.g., `/@docker/volumes/...`), making it unreliable for the path equality check.
- `docker inspect` queries the host Docker daemon which always returns the canonical host path. This is the only reliable method.
- Container self-inspection requires the Docker socket to be mounted — which is independently validated first (FR-035). The two checks are ordered: socket → path equality.
- This pattern is already implemented and tested in `entrypoint.sh` (L38-L67).

### Alternatives Considered
- `/proc/self/mountinfo` parsing: unreliable on Synology btrfs. Rejected.
- Symlink resolution (`readlink -f`): only works if the path exists on both ends; doesn't detect host-side path divergence. Rejected.

---

## Research Finding 7 — Runner version pinning and update procedure

### Current pinned version
- Runner: `v2.334.0` — SHA256: `048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271`
- Container hooks: `v0.8.1`

### Decision
Both versions are pinned in `ARG` declarations in the Dockerfile with SHA256 verification. Update procedure:
1. Check https://github.com/actions/runner/releases for latest stable release.
2. Download the `actions-runner-linux-x64-<version>.tar.gz` and compute its SHA256.
3. Update `RUNNER_VERSION` and `RUNNER_SHA256` ARGs in the Dockerfile.
4. Validate `CONTAINER_HOOKS_VERSION` against https://github.com/actions/runner-container-hooks/releases.
5. Open a PR → CI builds and validates → merge → GHCR publish.

### Rationale
- SHA256 verification during `docker build` provides supply-chain integrity: a tampered binary or a CDN compromise will cause build failure before any tainted binary reaches production.
- Pinned versions are mandatory for reproducible builds (FR-062).

---

## Research Finding 8 — GitHub API for registration token

### Decision
Use the GitHub REST API v3 with `Bearer` token auth and `X-GitHub-Api-Version: 2022-11-28` header.
- Org-level: `POST /orgs/{org}/actions/runners/registration-token`
- Repo-level: `POST /repos/{owner}/{repo}/actions/runners/registration-token`

### Required PAT scopes
| Target | Minimum scope |
|--------|---------------|
| Organization | `admin:org` (classic PAT) or fine-grained: `Organization self-hosted runners: write` |
| Repository | `repo` (classic PAT) or fine-grained: `Repository self-hosted runners: write` |

### Rationale
- GitHub App support is explicitly deferred to V2 per spec Assumptions.
- The `registration-token` API endpoint returns a short-lived token (valid for 1 hour) — enough to register and then `config.sh` stores its own long-lived credentials in `RUNNER_DIR/.credentials`.

---

## Summary: Decisions Table

| # | Topic | Decision | Key Constraint |
|---|-------|----------|---------------|
| 1 | EPHEMERAL default | `true` | GitHub security recommendation |
| 2 | Runner auto-update | Out of scope, `--disableupdate` | Reproducibility + FR-062 |
| 3 | Network retry | 3× retries, 10 s fixed interval | FR-015 fail-fast |
| 4 | Container hooks | Official, pinned v0.8.1, in image layer | DooD container:/services: |
| 5 | Socket GID | Dynamic at runtime (stat + groupadd) | Cross-host portability |
| 6 | Path mismatch check | `docker inspect` self | Synology btrfs reliability |
| 7 | Version pinning | ARG + SHA256 check in Dockerfile | FR-062 reproducibility |
| 8 | GitHub API auth | PAT Bearer token, API v2022-11-28 | FR-011, FR-012 |

---

## Diagnostic Finding 9 — Live `_diag/` Log Analysis (2026-05-14)

### Context

Log files collected from a real Ubuntu 24.04.4 LTS host running Runner v2.334.0. The runner was live and functional at the registration level; failures occurred exclusively during `container:` job execution.

### Key Confirmation: The Runner Starts Correctly

The registration path is **not** broken:
- Runner v2.334.0 successfully enrolled with GitHub → `Idle` state confirmed
- Jobs are received and handed off to the Worker process
- The failure is **exclusively** at the **Pre Job Hook** stage, when the Worker attempts to spawn a container for a `container:` job

**This eliminates** configuration issues, registration token problems, network connectivity, and GID resolution as root causes.

### Failure Sequence (confirmed from logs)

```
GitHub → dispatches job with container: ghcr.io/abenevaut/vapor-ci:php84
Runner → spawns Runner.Worker
Runner.Worker → pre-job hook: /opt/container-hooks/index.js
index.js → calls /usr/local/bin/docker (= docker-wrapper.sh)
docker-wrapper.sh → passes command to real /usr/bin/docker
/usr/bin/docker → exits with code 1 (daemon-level failure)
index.js → emits "failed" + errorMessages
Runner.Worker → exits with code 102 (container hook failure)
```

### Confirmed Log Evidence

**`Worker_20260514-184225-utc.log`**:
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

Worker exit code 102 in all runs → confirmed `actions/runner` internal code for container hook failure.

### Root Causes Identified (ordered by probability)

| ID | Cause | Priority | Resolution |
|----|-------|----------|-----------|
| CAUSE-01 | GHCR authentication missing — host daemon cannot pull private image | **HIGH** | DA-08: mount `~/.docker/config.json` or use `GHCR_TOKEN` |
| CAUSE-02 | Bind-mount source path absent at hook call time (`_work/_temp` etc.) | **HIGH** | Pre-create dirs in `entrypoint.sh` Phase 2 + harden `docker-wrapper.sh` |
| CAUSE-03 | container-hooks v0.8.1 incompatibility with runner v2.334.0 | **MEDIUM** | RISK-07: update hooks version, verify compatibility matrix |
| CAUSE-04 | `--network host` side-effects | LOW | Monitor; unlikely primary cause |

### Conclusion

The problem is **not** in the runner setup or registration. It is a Pre Job Hook failure that has **two concurrent high-priority causes** (CAUSE-01 and CAUSE-02) that must both be addressed. The architectural decision DA-08 and the RISK-01 sub-case in `plan.md` encode the required mitigations.
