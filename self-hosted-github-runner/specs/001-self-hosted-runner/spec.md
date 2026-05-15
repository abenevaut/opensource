# Feature Specification: Self-Hosted GitHub Actions Runner (Linux/AMD64, DooD-ready)

**Feature Branch**: `001-self-hosted-runner`
**Created**: 2026-03-19
**Status**: Draft
**Input**: User description: "Remise à plat de `self-hosted-github-runner` : image Docker linux/amd64 uniquement, démarrage fiable via `docker compose up`, enregistrement automatique auprès de GitHub (org ou repo), exécution de workflows utilisant `container:` / `services:` via DooD (socket Docker hôte), désenregistrement propre à l'arrêt, cibles Ubuntu standards et Synology DSM (Container Manager) avec contrainte de path identique host/container."

---

## Context

The project has gone through many iterative changes trying to make a self-hosted GitHub Actions runner work reliably as a Docker container, but it still fails in important scenarios (notably Synology Container Manager and workflows with `container:` / `services:`). This specification re-establishes the **functional contract** the project must satisfy before any refactor of the entrypoint, Dockerfile, compose file, and helper scripts.

The spec is deliberately implementation-agnostic. It describes **what** the runner image must do for its operators, not **how** the current scripts achieve it.

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Lancer un runner org-level fiable en une commande (Priority: P1)

En tant qu'**opérateur infra** (DevOps / sysadmin) sur un serveur Linux Ubuntu amd64, je veux pouvoir cloner ce projet, renseigner un fichier `.env` (token GitHub + nom d'organisation), lancer `docker compose up -d` et **voir le runner apparaître `Idle` dans l'UI GitHub Actions de mon organisation en moins de 2 minutes**, sans intervention manuelle additionnelle.

**Why this priority** : c'est la promesse fondamentale du projet. Si elle n'est pas tenue, aucun autre cas d'usage n'a de sens. Aujourd'hui ce flux échoue ou est instable selon l'environnement.

**Independent Test** : sur une VM Ubuntu 24.04 amd64 vierge avec Docker installé, exécuter le quickstart documenté avec un PAT valide ; le runner doit apparaître `Idle` dans `https://github.com/organizations/<ORG>/settings/actions/runners` et accepter un workflow trivial (`echo hello`).

**Acceptance Scenarios** :

1. **Given** un host Ubuntu amd64 avec Docker ≥ 20.10 et un PAT valide ayant le scope `admin:org` (ou permission fine-grained équivalente), **When** l'opérateur exécute `docker compose up -d` après avoir rempli `.env`, **Then** le conteneur démarre sans erreur et le runner est visible `Idle` côté GitHub dans les 120 secondes.
2. **Given** un runner `Idle`, **When** un workflow GitHub Actions trivial cible ce runner via `runs-on:`, **Then** le job est récupéré, exécuté avec succès, et son log apparaît côté GitHub.
3. **Given** un runner enregistré, **When** l'opérateur exécute `docker compose down` (SIGTERM), **Then** le runner est désenregistré côté GitHub avant l'arrêt du conteneur (il n'apparaît plus, ni en `Offline` orphelin, ni en `Idle`).
4. **Given** un PAT invalide ou expiré, **When** le conteneur démarre, **Then** il échoue **fast** avec un message d'erreur explicite mentionnant le token, et il ne reste pas en boucle silencieuse.

---

### User Story 2 — Exécuter des workflows utilisant `container:` ou `services:` (Priority: P1)

En tant que **développeur** déclenchant un workflow GitHub Actions depuis github.com, je veux pouvoir utiliser `jobs.<id>.container:` ou `services:` (ex. base de données pour des tests) sur ce runner self-hosted, **avec le même comportement que les runners hébergés par GitHub**, sans avoir à écrire de contournement spécifique dans les workflows.

**Why this priority** : c'est ce qui distingue ce runner d'un script shell trivial. Sans ce support, le runner ne peut pas exécuter la majorité des workflows réalistes (CI applicatif avec services, builds de containers, pipelines testant des contrats DB).

**Independent Test** : déclencher depuis github.com un workflow contenant `jobs.test.container: { image: ubuntu:24.04 }` ET un `services.postgres: { image: postgres:16 }` ; le job doit s'exécuter sans erreur de type "Bind mount failed: ... does not exist" et avoir accès au service postgres via DNS.

**Acceptance Scenarios** :

1. **Given** un runner enregistré et `Idle`, **When** un workflow utilise `jobs.<id>.container:`, **Then** le job s'exécute à l'intérieur du container demandé sans erreur de bind-mount, et les `actions/checkout`, `actions/cache`, etc. fonctionnent.
2. **Given** un workflow définissant un `services:` block, **When** ce workflow est déclenché, **Then** le service est démarré, accessible depuis le container du job par son nom DNS, et nettoyé en fin de job.
3. **Given** un workflow utilisant `docker/build-push-action` ou `docker buildx build`, **When** ce workflow tourne sur le runner, **Then** le build aboutit (le runner a accès au daemon Docker hôte) et l'image peut être poussée vers une registry.

---

### User Story 3 — Déploiement sur Synology DSM (Container Manager) sans casser DooD (Priority: P1)

En tant qu'**opérateur d'un NAS Synology Intel (amd64)**, je veux déployer ce runner via Container Manager (compose file ou wizard) en utilisant un chemin de stockage `/volume1/...`, et que les workflows utilisant `container:` / `services:` fonctionnent **sans configuration manuelle ésotérique**, ou à défaut que toute mauvaise configuration soit **détectée et signalée explicitement au démarrage** plutôt que de produire des erreurs cryptiques au premier job.

**Why this priority** : Synology est l'une des deux cibles de déploiement nommément supportées. Le path mismatch host/container (DooD) est la cause documentée principale d'échec aujourd'hui. La spec doit traiter ce cas explicitement.

**Independent Test** : sur un Synology DSM (amd64), créer le projet via Container Manager avec `RUNNER_DIR=/volume1/docker/actions-runner` et le bind-mount correspondant, déclencher un workflow avec `container:` ; le job doit réussir. Inversement, configurer volontairement un path différent host/container et vérifier que le conteneur **refuse de démarrer** avec un message diagnostic clair (chemin host attendu, chemin container actuel, exemple de correction).

**Acceptance Scenarios** :

1. **Given** un Synology DSM avec `RUNNER_DIR=/volume1/docker/actions-runner` correctement bind-monté en `/volume1/docker/actions-runner:/volume1/docker/actions-runner`, **When** le conteneur démarre, **Then** il s'enregistre avec succès et exécute un workflow `container:` sans erreur.
2. **Given** une configuration où le path host ≠ path container (ex. `/volume1/docker-volumes/runner_data:/home/docker/actions-runner`), **When** le conteneur démarre, **Then** il échoue **fast** au démarrage avec un message indiquant les deux paths et la correction attendue, **avant** d'enregistrer un runner zombie côté GitHub.
3. **Given** une configuration où le socket Docker n'est pas monté, **When** le conteneur démarre, **Then** il échoue **fast** avec un message explicite réclamant `-v /var/run/docker.sock:/var/run/docker.sock`.

---

### User Story 4 — Configurabilité minimale par variables d'environnement (Priority: P2)

En tant qu'**opérateur**, je veux pouvoir configurer **tous** les paramètres essentiels du runner uniquement via des variables d'environnement (pas de fichier de config interne, pas d'argument de runtime), pour intégration triviale avec `.env`, secrets Docker, secrets Kubernetes, secrets Synology, etc.

**Why this priority** : pré-requis pour automatisation et pour ne pas baker de secrets dans l'image. P2 car la US1 le suppose implicitement, mais mérite d'être explicité comme contrat.

**Independent Test** : démarrer le conteneur deux fois avec deux jeux d'env différents (un repo-level avec labels custom, un org-level avec nom custom) sans modifier ni l'image ni un fichier monté ; les deux runners s'enregistrent avec les bons paramètres.

**Acceptance Scenarios** :

1. **Given** uniquement `ORGANIZATION` + `ACCESS_TOKEN` définis, **When** le conteneur démarre, **Then** un runner org-level s'enregistre avec un nom et des labels par défaut documentés.
2. **Given** `ORGANIZATION` + `REPOSITORY` + `ACCESS_TOKEN` + `RUNNER_LABELS=self-hosted,linux,x64,custom`, **When** le conteneur démarre, **Then** un runner repo-level s'enregistre avec exactement ces labels.
3. **Given** un placeholder par défaut non remplacé (ex. `ACCESS_TOKEN=<YOUR-GITHUB-ACCESS-TOKEN>`), **When** le conteneur démarre, **Then** il refuse de démarrer avec un message explicite.

---

### User Story 5 — Image publiée et reproductible (Priority: P2)

En tant qu'**opérateur** ou **mainteneur**, je veux que l'image soit publiée sur un registre public (GHCR), buildée **uniquement pour `linux/amd64`**, et que sa version (du runner GitHub embarqué et de l'image elle-même) soit traçable, pour pouvoir épingler une version dans un environnement de production.

**Why this priority** : nécessaire pour que les opérateurs ne dépendent pas d'un build local. Pas P1 car un opérateur peut toujours builder localement à partir du source.

**Independent Test** : `docker pull ghcr.io/abenevaut/self-hosted-github-runner:<tag>` sur un host amd64 fonctionne ; l'inspection de l'image montre `Architecture: amd64` uniquement (pas de manifest multi-arch) ; un tag versionné distinct du `latest` est disponible.

**Acceptance Scenarios** :

1. **Given** l'image publiée, **When** un opérateur la `docker pull` depuis un host amd64, **Then** l'image est récupérée sans avertissement de mismatch d'architecture.
2. **Given** l'image publiée, **When** un opérateur tente de la `docker pull` depuis un host arm64, **Then** Docker rapporte clairement l'absence de variante arm64 (pas de fallback silencieux vers amd64 qui crasherait à l'exécution).
3. **Given** une release de l'image, **When** un opérateur consulte le registre, **Then** au moins deux tags coexistent : un tag mouvant (`latest`) et un tag immuable (versionné, ex. date ou semver) permettant d'épingler la version.

---

### Edge Cases

- **PAT révoqué pendant l'exécution** : si le token est révoqué entre l'enregistrement initial et un job futur, le runner doit logger l'erreur côté API GitHub et s'arrêter proprement (pas de boucle infinie de retries silencieux).
- **Perte temporaire de connectivité réseau vers github.com** : le runner doit retenter l'enregistrement initial avec backoff plutôt que d'échouer immédiatement, dans une fenêtre raisonnable. **[NEEDS CLARIFICATION: durée et stratégie de retry au démarrage non spécifiées — fail fast après 30 s, ou retry exponentiel pendant 5 min ?]**
- **Crash brutal du conteneur (SIGKILL, OOM)** : un runner orphelin peut rester côté GitHub. Ce cas n'est **pas couvert** par cette spec : l'opérateur doit le nettoyer manuellement ou via un nouveau démarrage avec `--replace`.
- **Premier démarrage sur volume vierge** : le contenu nécessaire au runtime (binaire runner, scripts) doit être provisionné sur le volume hôte sans intervention.
- **Re-démarrage sur volume déjà initialisé** : le démarrage doit être idempotent et plus rapide (pas de re-téléchargement, pas de double enregistrement).
- **Permissions sur le bind-mount** : si le UID/GID hôte du dossier `RUNNER_DIR` ne permet pas l'écriture par l'utilisateur du conteneur, le conteneur doit échouer fast avec un message explicite plutôt que silencieusement.
- **Socket Docker avec GID hôte différent du GID baked dans l'image** : doit être géré dynamiquement au démarrage (l'utilisateur runner doit pouvoir accéder au socket sans `chmod 666`).
- **Workflow déclenché juste avant un `docker compose down`** : un job en cours doit pouvoir terminer (ou au minimum être interrompu proprement et signalé en échec côté GitHub) ; le désenregistrement ne doit pas laisser un job "pending" sans worker.
- **Mode éphémère vs persistant** : voir Q2 ci-dessous (NEEDS CLARIFICATION).
- **Tentative de build/run sur un host non-amd64** : doit être clairement diagnostiquée par le `platform: linux/amd64` du compose et/ou par un check explicite au démarrage.
- **Opérateur change `container_path` sans changer `host_path`** : par exemple, l'opérateur monte `/volume1/docker/actions-runner:/home/docker/actions-runner` en pensant utiliser un chemin "standard" côté conteneur. Comportement attendu : la Gate 2 de l'entrypoint détecte que le chemin vu depuis l'intérieur du conteneur (`/home/docker/actions-runner`) ne correspond pas au chemin réel sur l'hôte (`/volume1/docker/actions-runner`), et le conteneur échoue **fast** avant toute enregistrement avec un message du type :
  ```
  [runner] FATAL: DooD path mismatch detected.
    Container RUNNER_DIR : /home/docker/actions-runner
    Host source path     : /volume1/docker/actions-runner
  These must be identical for DooD container:/services: jobs to work.
  Fix: use  -v /volume1/docker/actions-runner:/volume1/docker/actions-runner
       and  RUNNER_DIR=/volume1/docker/actions-runner
  ```
- **Image job-container privée sans credentials** : si un workflow utilise `container: ghcr.io/<org>/<image>` (image privée) et qu'aucune credential n'est configurée (`DOCKER_CONFIG_HOST` ou `GHCR_TOKEN` absents), le Pre Job Hook échoue avec `docker exit 1` et Worker exit code 102. Ce scénario est documenté comme un **fail-fast avec suggestion** : le log du runner doit indiquer que l'erreur provient probablement d'une absence de credentials registry, et suggérer de monter `~/.docker/config.json` via `DOCKER_CONFIG_HOST` ou de configurer `GHCR_TOKEN`. (Voir FR-072, CAUSE-01, DA-08 dans `plan.md`.)

---

## Requirements *(mandatory)*

### Functional Requirements

#### Architecture & plate-forme

- **FR-001** : L'image Docker MUST être buildée et publiée **exclusivement pour `linux/amd64`**. Aucun manifest multi-architecture (arm64, arm/v7, etc.) ne doit être produit.
- **FR-002** : Le projet MUST cibler comme hôtes officiels supportés : (a) serveurs Linux Ubuntu 64-bit (amd64), (b) Synology DSM (Container Manager) sur matériel Intel/AMD x86_64. Tout autre OS hôte (macOS, Windows, ARM) est explicitement hors scope.
- **FR-003** : L'image MUST être basée sur une distribution Linux LTS supportée et reproductible (version épinglée dans le Dockerfile).

#### Démarrage, enregistrement, arrêt

- **FR-010** : Au lancement via `docker compose up`, le conteneur MUST démarrer sans erreur quand `.env` est correctement renseigné (pas de manipulation manuelle additionnelle requise sur l'host au-delà de la création du dossier `RUNNER_DIR`).
- **FR-011** : Le runner MUST s'enregistrer automatiquement auprès de GitHub au démarrage, sans intervention humaine, en utilisant l'API de registration token.
- **FR-012** : Le runner MUST supporter à la fois l'enregistrement **org-level** (variable `ORGANIZATION` seule) ET **repo-level** (variables `ORGANIZATION` + `REPOSITORY`).
- **FR-013** : Une fois enregistré, le runner MUST apparaître `Idle` dans l'UI GitHub Actions cible dans un délai raisonnable (objectif < 120 s sur connexion standard).
- **FR-014** : À la réception d'un signal d'arrêt propre (`SIGTERM`, `SIGINT`), le conteneur MUST désenregistrer le runner côté GitHub **avant** de s'arrêter.
- **FR-015** : Si l'enregistrement initial échoue (token invalide, organisation introuvable, réseau indisponible au-delà de la fenêtre de retry), le conteneur MUST échouer **fast** avec un message d'erreur sans ambiguïté pointant la cause probable, et MUST NOT rester en exécution sans runner enregistré.
- **FR-016** : Le démarrage MUST être idempotent : un re-démarrage sur un volume `RUNNER_DIR` déjà initialisé ne doit pas re-télécharger inutilement le binaire runner ni produire d'enregistrement zombie.

#### Configuration

- **FR-020** : La configuration du runner MUST être entièrement pilotée par variables d'environnement (pas de fichier de config interne édité par l'utilisateur, pas d'argument de `docker run` autre que les flags Docker standards). Variables minimales :
  - **Required** : `ACCESS_TOKEN`, `ORGANIZATION`
  - **Optional** : `REPOSITORY`, `RUNNER_NAME`, `RUNNER_LABELS`, `RUNNER_WORKDIR`, `RUNNER_DIR`, `EPHEMERAL`, `DOCKER_CONFIG_HOST`, `GHCR_TOKEN`
- **FR-021** : Des valeurs par défaut documentées MUST être appliquées pour toutes les variables optionnelles (ex. `RUNNER_LABELS=self-hosted,linux,x64`, `RUNNER_NAME=$(hostname)`).
- **FR-022** : Si une variable obligatoire est manquante OU contient un placeholder par défaut non remplacé (ex. `<YOUR-GITHUB-...>`), le démarrage MUST échouer fast avec un message explicite.
- **FR-023** : Un fichier `.env.example` MUST être fourni à la racine du projet, listant toutes les variables avec leur statut (required/optional), valeur par défaut, et description courte.

#### Exécution de workflows utilisant Docker (DooD)

- **FR-030** : Le runner MUST permettre l'exécution de workflows GitHub Actions utilisant `jobs.<id>.container:` et `services:` déclenchés depuis github.com, **sans modification du workflow** par rapport à un runner GitHub-hosted.
- **FR-031** : Le runner MUST utiliser le pattern **DooD (Docker-outside-of-Docker)** : accès au daemon Docker de l'hôte via le socket `/var/run/docker.sock` monté en lecture/écriture. Le mode rootless ou Docker-in-Docker n'est **pas** supporté.
- **FR-032** : Le runner MUST permettre l'exécution de `docker build`, `docker push`, `docker buildx build`, `docker compose`, et l'action `docker/build-push-action` dans les jobs.
- **FR-033** : Pour que les jobs `container:` / `services:` fonctionnent, le projet MUST garantir (ou détecter et signaler l'absence de) la cohérence entre le chemin hôte et le chemin conteneur du dossier `RUNNER_DIR` — ces deux paths doivent être **identiques** car le daemon Docker hôte résout les sources de bind-mount sur le filesystem hôte.
- **FR-034** : Au démarrage, le runner MUST vérifier explicitement cette cohérence host-path == container-path et MUST échouer fast (avec un message indiquant les deux paths et un exemple de correction) si elle n'est pas respectée.
- **FR-035** : Au démarrage, le runner MUST vérifier que le socket Docker hôte est monté ; si absent, MUST échouer fast avec un message explicite.
- **FR-036** : L'utilisateur exécutant le runner à l'intérieur du conteneur MUST avoir accès au socket Docker hôte, quel que soit le GID hôte du socket (résolution dynamique au démarrage).

#### Sécurité

- **FR-040** : Le runner MUST s'exécuter en tant qu'utilisateur **non-root** à l'intérieur du conteneur pour la phase d'exécution des jobs (une phase d'init root est tolérée pour préparer l'environnement, puis drop des privilèges).
- **FR-041** : La documentation MUST avertir explicitement que (a) monter le socket Docker confère un accès root effectif au daemon hôte, et (b) il est dangereux d'utiliser ce runner sur des dépôts publics acceptant des PRs externes.
- **FR-042** : Le token GitHub MUST NOT être logué en clair, ni écrit dans le filesystem du volume `RUNNER_DIR` au-delà de l'usage interne du runner officiel.

#### Mode éphémère

- **FR-050** : Le runner MUST supporter le mode **éphémère** (un job = un démarrage, désenregistrement après le job) via la variable `EPHEMERAL=true`. **[NEEDS CLARIFICATION: `EPHEMERAL=true` doit-il rester la valeur par défaut (sécurité maximum, recommandation officielle GitHub) ou repasser à `false` (simplicité opérationnelle, un seul démarrage = N jobs) ?]**
- **FR-051** : Le mode persistant (`EPHEMERAL=false`) MUST aussi être supporté pour les opérateurs qui le préfèrent.

#### Cycle de vie de l'image

- **FR-060** : L'image MUST être publiée sur un registre public (GHCR : `ghcr.io/abenevaut/self-hosted-github-runner`).
- **FR-061** : Au moins deux tags MUST exister : un tag mouvant (`latest`) et un tag immuable permettant d'épingler une version reproductible.
- **FR-062** : La version du binaire GitHub Actions Runner embarqué MUST être épinglée dans le Dockerfile (avec checksum vérifié au build), et la procédure de mise à jour de cette version MUST être documentée.
- **FR-063** : **[NEEDS CLARIFICATION: L'auto-update du binaire runner (mécanisme natif de GitHub où le runner se met à jour seul quand GitHub force une nouvelle version minimum) est-il dans le scope de cette spec — auquel cas il faut le supporter et persister proprement entre redémarrages — ou explicitement hors scope, l'image étant toujours rebuildée et republiée pour suivre la dernière version ?]**

#### Diagnostic & observabilité

- **FR-070** : Tous les logs du conteneur (entrypoint, runner, jobs) MUST être émis sur `stdout`/`stderr` pour être collectés par `docker logs` / `docker compose logs` sans configuration additionnelle.
- **FR-071** : Les messages d'échec au démarrage MUST inclure : la cause détectée, la valeur observée (path, GID, code HTTP, etc.), et au moins un exemple de correction.
- **FR-072** : Le runner MUST propager les credentials Docker nécessaires au daemon hôte pour puller des images de containers privées lors des jobs `container:`. L'opérateur DOIT pouvoir fournir ces credentials via **l'une des deux mécanismes** suivants :
  - **(A)** Variable optionnelle `DOCKER_CONFIG_HOST` (chemin hôte vers un répertoire contenant `config.json`) → monté en lecture seule à `/home/docker/.docker` dans le conteneur.
  - **(B)** Variable optionnelle `GHCR_TOKEN` → `entrypoint.sh` Phase 1 exécute `docker login ghcr.io` avant de lancer le runner.
  - Si aucun des deux n'est fourni et que le job utilise une image privée, le Pre Job Hook MUST échouer avec un exit code 1 et le log MUST indiquer clairement qu'une erreur d'authentification registry est probable.

### Key Entities

- **Runner Container** : instance Docker qui exécute le runner. Attributs clés : `RUNNER_DIR` (path identique host/container), socket Docker monté, utilisateur non-root, état de cycle de vie (init / registered / running job / draining / removed).
- **GitHub Registration Target** : organisation OU dépôt cible. Identifié par `ORGANIZATION` (+ `REPOSITORY` optionnel). Détermine l'endpoint d'API utilisé pour obtenir le registration token et l'URL d'enregistrement.
- **Runner Identity** : nom (`RUNNER_NAME`), labels (`RUNNER_LABELS`), groupe — utilisés côté GitHub pour le routage des jobs (`runs-on:`).
- **Host Volume `RUNNER_DIR`** : dossier sur l'hôte contenant l'installation runtime du runner et son `_work` directory. **Contrainte structurelle** : son chemin absolu hôte DOIT être strictement égal à son point de montage dans le conteneur.
- **Host Docker Socket** : `/var/run/docker.sock` de l'hôte, monté tel quel dans le conteneur. C'est la dépendance externe critique pour DooD.
- **Personal Access Token / GitHub App credential** : secret d'autorisation pour appeler l'API d'enregistrement. Scopes requis documentés selon la cible (org vs repo).

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001** : Sur un host Ubuntu 24.04 amd64 vierge avec Docker installé, un opérateur peut passer de "git clone" à un runner `Idle` côté GitHub en **moins de 5 minutes**, en suivant uniquement le README, sans intervention de support.
- **SC-002** : Sur un Synology DSM Intel avec Container Manager, un opérateur peut atteindre le même résultat en moins de 10 minutes en suivant la section dédiée du README.
- **SC-003** : 100 % des configurations invalides parmi la liste suivante doivent produire une erreur explicite **au démarrage** (et pas un comportement silencieux ou un runner zombie côté GitHub) : token absent ou placeholder ; `ORGANIZATION` absente ou placeholder ; socket Docker non monté ; path host ≠ path container pour `RUNNER_DIR` ; permissions insuffisantes sur `RUNNER_DIR`.
- **SC-004** : Un workflow contenant un `jobs.<id>.container:` ET un `services:` doit s'exécuter avec succès sur ce runner sans modification du workflow par rapport à son exécution sur un runner GitHub-hosted (test de référence partagé).
- **SC-005** : À l'envoi de `SIGTERM` (`docker compose down`), le runner est désenregistré côté GitHub dans **moins de 30 secondes** (hors job en cours d'exécution) — vérifiable via l'API ou l'UI GitHub.
- **SC-006** : Aucun runner zombie (`Offline` non supprimé) ne reste côté GitHub après un cycle complet `up` → `Idle` → `down` exécuté dix fois consécutives.
- **SC-007** : L'image publiée pèse **moins de 1.5 GB compressée** (ordre de grandeur, à ajuster en fonction des dépendances réellement embarquées) et démarre en **moins de 30 secondes** sur volume déjà initialisé.
- **SC-008** : 0 erreur de type "Bind mount failed: ... does not exist" sur un panel de référence de 5 workflows utilisant `container:`/`services:` exécutés successivement.
- **SC-009** : La documentation README couvre les deux cibles (Ubuntu standard, Synology DSM) avec des exemples copy-paste qui fonctionnent tels quels.

---

## Assumptions

- L'opérateur a accès **root** ou `sudo` sur l'hôte pour créer le dossier `RUNNER_DIR` une seule fois et pour lancer `docker compose`.
- L'hôte dispose d'une connectivité sortante non filtrée vers `github.com`, `api.github.com`, `ghcr.io`, et `download.docker.com` (ou équivalent miroir).
- Le PAT fourni a au minimum le scope `admin:org` (org-level) ou `repo` (repo-level), conformément aux exigences GitHub. **L'usage d'une GitHub App n'est pas couvert dans cette itération** (à reconsidérer en V2).
- Le projet ne cible pas l'auto-scaling à l'échelle (1 à quelques runners). Pour un usage massif, ARC (Actions Runner Controller sur Kubernetes) est l'outil recommandé, hors scope ici.
- L'hôte n'utilise pas de socket Docker rootless ni un daemon distant (TCP). Seul le socket UNIX local est supporté.
- Le runner est destiné à des **dépôts privés** ou à des dépôts publics sans PRs externes acceptées.
- Les workflows exécutés sont **de confiance** (le DooD donne un accès root effectif au daemon hôte).
- Les valeurs par défaut placeholder (`<YOUR-...>`) dans `.env.example` sont volontairement non fonctionnelles pour empêcher un démarrage muet.
- L'image publique sur GHCR est buildée par une CI (workflow `workflow_dispatch` sur GitHub Actions hosted runners), pas par les opérateurs eux-mêmes.

---

## Out of Scope

Pour éviter toute ambiguïté, les éléments suivants sont **explicitement exclus** de cette spec :

- Support **multi-architecture** : pas de build arm64, arm/v7, ppc64le, s390x.
- Support **macOS hôte** ou **Windows hôte** (Docker Desktop ne fournit pas un socket Docker hôte au sens entendu ici, et le path mismatch est par nature non résoluble).
- Support **Docker rootless** (incompatible avec la résolution de paths DooD requise pour `container:`).
- Support **Docker-in-Docker (DinD)** : un daemon Docker complet à l'intérieur du conteneur n'est pas la stratégie retenue.
- **Auto-scaling** / orchestration de pools de runners (renvoyé vers ARC).
- Intégration **Kubernetes / Helm chart** dédiés.
- **Monitoring/metrics** (Prometheus exporter, healthcheck custom) au-delà de `docker logs`.
- Support de runners **macOS** ou **Windows** côté exécution de jobs (le runner exécute des jobs Linux uniquement).

---

