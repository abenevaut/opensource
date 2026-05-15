# self-hosted-github-runner

Image Docker minimaliste pour exécuter un [GitHub Actions self-hosted runner](https://docs.github.com/en/actions/hosting-your-own-runners) au niveau **organisation** ou **dépôt**, en mode **éphémère** par défaut (un job = un conteneur, recommandé pour la sécurité et la reproductibilité).

> Image publiée : `ghcr.io/abenevaut/self-hosted-github-runner:latest`

> ## ⚠️ AMD64 / x86_64 UNIQUEMENT
>
> **Cette image ne supporte que `linux/amd64` (Intel/AMD x86_64).** Tout build/run sur `linux/arm64` (Apple Silicon, AWS Graviton, Raspberry Pi, NAS ARM) **n'est pas supporté et ne fonctionnera pas** : le binaire .NET du GitHub Actions Runner échoue lors de l'enregistrement avec des erreurs de désérialisation (`Unable to find a constructor for VssJsonCollectionWrapper`, `EmitDefaultValue property not found`, `AccessViolationException`) à cause d'une incompatibilité du JIT .NET sur ARM64 sous Docker.
>
> Cibles validées : serveurs Linux x86_64 (bare-metal, VPS, NAS Synology Intel, etc.). **Pas Mac M1/M2/M3, pas Raspberry Pi, pas Synology ARM.**

---

## Sommaire

- [Caractéristiques](#caractéristiques)
- [Prérequis](#prérequis)
- [Quickstart Ubuntu / Linux](#quickstart-ubuntu--linux)
- [Quickstart Synology DSM](#quickstart-synology-dsm)
- [Build local](#build-local)
- [Publication sur GHCR](#publication-sur-ghcr)
- [Lancer un runner](#lancer-un-runner)
  - [Au niveau organisation](#au-niveau-organisation)
  - [Au niveau dépôt](#au-niveau-dépôt)
  - [Avec docker compose](#avec-docker-compose)
- [Variables d'environnement](#variables-denvironnement)
- [Mode éphémère](#mode-éphémère)
- [Authentification images privées](#authentification-images-privées-da-08)
- [Sécurité](#sécurité)
- [Mettre à jour la version du runner](#mettre-à-jour-la-version-du-runner)
- [Pour aller plus loin : ARC](#pour-aller-plus-loin--arc)

---

## Caractéristiques

- **`linux/amd64` uniquement** (voir bandeau ci-dessus)
- Base **Ubuntu 24.04 LTS**
- GitHub Actions Runner **2.334.0** avec vérification SHA-256 du tarball
- **Container hooks 0.8.1** — supporte les workflows utilisant `container:` / `services:` depuis l'intérieur d'un runner conteneurisé (DooD)
- Utilisateur non-root `docker` (UID 1000, avec `sudo` NOPASSWD)
- Auto-update du runner désactivé (`--disableupdate`) — version contrôlée via l'image Docker
- Enregistrement automatique au démarrage, désenregistrement automatique à l'arrêt (`SIGINT` / `SIGTERM`)
- Support **org-level** ET **repo-level**
- Mode **éphémère** activé par défaut (`EPHEMERAL=true`)
- Étiquettes (`labels`) et nom de runner configurables
- **Docker CLI** inclus (`docker`, `docker buildx`, `docker compose`) — accès au daemon hôte via socket passthrough (DooD)
- **Wrapper `docker`** intégré qui pré-crée les bind-mounts `_work/_temp/_github_home` etc. pour fiabiliser les jobs `container:`
- **Authentification GHCR** via `GHCR_TOKEN` ou montage du config Docker hôte (`DOCKER_CONFIG_HOST`)

## Prérequis

- Docker ≥ 20.10 avec Docker Compose plugin
- Hôte **`linux/amd64`** (voir bandeau)
- Un **Personal Access Token** GitHub :
  - **Org-level** : scope `admin:org` (classic PAT) ou permissions `Self-hosted runners: Read & write` (fine-grained PAT, organisation entière).
  - **Repo-level** : scope `repo` (classic) ou permissions `Administration: Read & write` sur le dépôt (fine-grained).
- Le nom de l'organisation (et éventuellement du dépôt).

---

## Quickstart Ubuntu / Linux

Accès rapide à un runner Idle en moins de 5 minutes.

```bash
# 1. Cloner le projet
git clone https://github.com/abenevaut/self-hosted-github-runner.git
cd self-hosted-github-runner

# 2. Créer le répertoire runner (chemin identique hôte ↔ container — contrainte DooD)
sudo mkdir -p /home/docker/actions-runner
sudo chown $USER:$USER /home/docker/actions-runner

# 3. Configurer l'environnement
cp .env.example .env
# Éditer .env : renseigner ORGANIZATION et ACCESS_TOKEN

# 4. Démarrer
docker compose up -d

# 5. Vérifier
docker compose logs -f gh-runner
# Résultat attendu : "[runner] ✓ Runner configured and ready"
# Puis vérifier : https://github.com/organizations/<VOTRE-ORG>/settings/actions/runners
```

> **Timing cible (SC-001)** : runner visible `Idle` dans GitHub Actions < 5 min depuis `git clone`.

---

## Quickstart Synology DSM

> ⚠️ Sur Synology, le vrai chemin de stockage est `/volume1/...` — PAS `/home/docker/...`.  
> Le montage ci-dessous utilise `/volume1/docker/actions-runner` **des deux côtés**, ce qui est **correct et obligatoire** (contrainte DooD de chemin identique).

```bash
# Via SSH dans le Synology :
sudo mkdir -p /volume1/docker/actions-runner
```

Dans Container Manager → Projects → Créer avec compose :

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
      # RUNNER_DIR doit correspondre exactement au chemin hôte (contrainte DooD)
      - RUNNER_DIR=/volume1/docker/actions-runner
      - EPHEMERAL=true
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      # ✅ /volume1/docker/actions-runner des DEUX côtés — correct et obligatoire.
      #    Le daemon hôte résout ${RUNNER_DIR}/_work sur le filesystem hôte.
      #    Utiliser /home/docker/... comme path conteneur CASSERAIT les jobs DooD.
      - /volume1/docker/actions-runner:/volume1/docker/actions-runner
```

L'entrypoint vérifie automatiquement la contrainte de chemin via `docker inspect` et **fail fast** avec un message explicite si le path hôte ≠ path conteneur (voir [quickstart.md](./specs/001-self-hosted-runner/quickstart.md) pour le diagramme complet).

> **Timing cible (SC-002)** : runner visible `Idle` dans GitHub Actions < 10 min depuis le démarrage du container.

---

## Build local

```bash
cd self-hosted-github-runner
docker build --platform linux/amd64 --tag ghcr.io/abenevaut/self-hosted-github-runner:latest .
```

Pour builder une autre version du runner :

```bash
docker build --platform linux/amd64 \
  --build-arg RUNNER_VERSION=2.334.0 \
  --build-arg RUNNER_SHA256=048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271 \
  --tag ghcr.io/abenevaut/self-hosted-github-runner:2.334.0 \
  .
```

## Publication sur GHCR

Une [pipeline GitHub Actions](.github/workflows/publish.yml) (`workflow_dispatch`) build et publie l'image `linux/amd64` sur GHCR avec les tags `latest`, `YYYY.MM.DD`, et `runner-vX.Y.Z`.

Manuellement :

```bash
echo "$GITHUB_TOKEN" | docker login ghcr.io -u abenevaut --password-stdin
docker push ghcr.io/abenevaut/self-hosted-github-runner:latest
```

## Lancer un runner

### Au niveau organisation

```bash
sudo mkdir -p /home/docker/actions-runner
docker run -d --restart=always --name gh-runner \
  -e ORGANIZATION=abenevaut \
  -e ACCESS_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v /home/docker/actions-runner:/home/docker/actions-runner \
  ghcr.io/abenevaut/self-hosted-github-runner:latest
```

### Au niveau dépôt

```bash
sudo mkdir -p /home/docker/actions-runner
docker run -d --restart=always --name gh-runner-myrepo \
  -e ORGANIZATION=abenevaut \
  -e REPOSITORY=mon-repo \
  -e ACCESS_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx \
  -e RUNNER_LABELS=self-hosted,linux,x64,myrepo \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v /home/docker/actions-runner:/home/docker/actions-runner \
  ghcr.io/abenevaut/self-hosted-github-runner:latest
```

> Le volume `RUNNER_DIR:RUNNER_DIR` (même chemin hôte = conteneur) est **obligatoire** pour les workflows utilisant `container:` ou `services:`. Les container hooks transmettent ce chemin au daemon Docker du hôte qui le résout sur le filesystem hôte.

### ⚠️ Synology DSM (Container Manager)

Sur Synology, **n'utilisez pas `/home/docker/...`** : ce chemin n'existe pas en tant que vrai répertoire hôte. Utilisez `/volume1/...`. Et surtout : **le path hôte doit être identique au path conteneur** (sinon le daemon échoue avec `Bind mount failed: '/home/docker/actions-runner/_work' does not exist`).

❌ **Mauvaise config courante** (path différent de chaque côté → casse `container:` jobs) :
```yaml
volumes:
  - /volume1/docker-volumes/runner_data:/home/docker/actions-runner
```

✅ **Bonne config Synology** (même path des deux côtés) :
```bash
sudo mkdir -p /volume1/docker/actions-runner
```
```yaml
# .env
RUNNER_DIR=/volume1/docker/actions-runner
```
```yaml
# docker-compose.yml (déjà géré : ${RUNNER_DIR}:${RUNNER_DIR})
volumes:
  - /var/run/docker.sock:/var/run/docker.sock
  - /volume1/docker/actions-runner:/volume1/docker/actions-runner
```

L'entrypoint vérifie cette contrainte au démarrage via `docker inspect` sur son propre conteneur et **fail fast** avec un message explicite si le path hôte ≠ path conteneur.

### ⚠️ Synology DSM (Container Manager)

Sur Synology, **n'utilisez pas `/home/docker/...`** : ce chemin n'existe pas en tant que vrai répertoire hôte. Utilisez `/volume1/...`. Et surtout : **le path host doit être identique au path conteneur** (sinon le daemon échoue avec `Bind mount failed: '/home/docker/actions-runner/_work' does not exist`).

❌ **Mauvaise config courante** (path différent de chaque côté → casse `container:` jobs) :
```yaml
volumes:
  - /volume1/docker-volumes/runner_data:/home/docker/actions-runner
```

✅ **Bonne config Synology** (même path des deux côtés) :
```bash
sudo mkdir -p /volume1/docker/actions-runner
```
```yaml
# .env
RUNNER_DIR=/volume1/docker/actions-runner
```
```yaml
# docker-compose.yml (déjà géré : ${RUNNER_DIR}:${RUNNER_DIR})
volumes:
  - /var/run/docker.sock:/var/run/docker.sock
  - /volume1/docker/actions-runner:/volume1/docker/actions-runner
```

L'entrypoint vérifie cette contrainte au démarrage via `docker inspect` sur son propre conteneur et **fail fast** avec un message explicite si le path host ≠ path conteneur.

### Avec docker compose

Copiez le fichier d'environnement et renseignez vos valeurs :

```bash
cp .env.example .env
# éditez .env puis :
sudo mkdir -p /home/docker/actions-runner
docker compose up -d
```

Le `docker-compose.yml` fourni lit les variables depuis `.env` et configure :
- le socket Docker passthrough (`/var/run/docker.sock`)
- le bind-mount `RUNNER_DIR:RUNNER_DIR` requis par les container hooks
- la plate-forme `linux/amd64` explicitement
- un healthcheck via fichier sentinelle (`${RUNNER_DIR}/.runner-ready`)

> Le volume `/var/run/docker.sock` passe le socket Docker du **hôte** dans le conteneur (pattern **DooD** — Docker-outside-of-Docker). Le runner peut ainsi exécuter `docker build`, `docker push`, `docker compose`, `docker/build-push-action`, `container:`, `services:`, etc. sans avoir à faire tourner un daemon Docker secondaire à l'intérieur du conteneur.

## Variables d'environnement

| Variable | Obligatoire | Défaut | Description |
|----------|:-----------:|--------|-------------|
| `ORGANIZATION` | ✅ | `<YOUR-GITHUB-ORGANIZATION>` | Nom de l'organisation (ou owner) GitHub. |
| `ACCESS_TOKEN` | ✅ | `<YOUR-GITHUB-ACCESS-TOKEN>` | PAT avec scope `admin:org` (org) ou `repo` (repo). |
| `RUNNER_DIR` | ❌ | `/home/docker/actions-runner` | Chemin du runner sur l'hôte ET dans le conteneur (doit être identique). |
| `REPOSITORY` | ❌ | *(vide)* | Si défini, enregistre le runner au niveau du dépôt au lieu de l'organisation. |
| `RUNNER_NAME` | ❌ | `$(hostname)` | Nom affiché dans GitHub. |
| `RUNNER_LABELS` | ❌ | `self-hosted,linux,x64` | Labels (séparés par des virgules) pour cibler ce runner depuis `runs-on:`. |
| `RUNNER_WORKDIR` | ❌ | `_work` | Sous-dossier de travail du runner (relatif à `RUNNER_DIR`). |
| `EPHEMERAL` | ❌ | `true` | Si `true`, le runner se désenregistre après un seul job. |
| `GHCR_TOKEN` | ❌ | *(vide)* | Token GHCR pour `docker login ghcr.io` (auth images privées, Option B). |
| `GHCR_USER` | ❌ | `x-token` | Username pour le login GHCR (utilisé avec `GHCR_TOKEN`). |
| `DOCKER_CONFIG_HOST` | ❌ | *(vide)* | Chemin hôte du répertoire Docker config (auth images privées, Option A). |
| `ACTIONS_RUNNER_CONTAINER_NETWORK` | ❌ | `bridge` | Réseau Docker pour les containers de job. |

> Les valeurs placeholder pour `ORGANIZATION` et `ACCESS_TOKEN` provoquent un échec immédiat au démarrage si elles ne sont pas surchargées — c'est volontaire pour éviter de lancer un conteneur "muet".

## Mode éphémère

Avec `EPHEMERAL=true` (défaut), le runner exécute **un seul job** puis se désenregistre proprement et le conteneur s'arrête. Combiné à `restart: always`, Docker en relance immédiatement un nouveau, ce qui garantit un environnement propre pour chaque job.

Pour un runner persistant (legacy) :

```bash
docker run -d --restart=always \
  -e EPHEMERAL=false \
  -e ORGANIZATION=abenevaut \
  -e ACCESS_TOKEN=ghp_xxx \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v /home/docker/actions-runner:/home/docker/actions-runner \
  ghcr.io/abenevaut/self-hosted-github-runner:latest
```

---

## Authentification images privées (DA-08)

> **Contexte** : quand un workflow utilise `container: ghcr.io/your-org/your-image:tag` (image privée), le Pre Job Hook appelle `/usr/local/bin/docker create` pour créer le container. Le **daemon Docker hôte** doit puller l'image — or il n'hérite pas automatiquement des credentials du runner. Sans configuration, le hook échoue avec exit code 1 (**Worker exit code 102**).

### Option A — Monter le config Docker hôte (recommandée, multi-registres)

Montez le répertoire `~/.docker` du hôte en lecture seule dans le container :

```yaml
# docker-compose.yml — dans la section volumes:
volumes:
  - /var/run/docker.sock:/var/run/docker.sock
  - ${RUNNER_DIR:-/home/docker/actions-runner}:${RUNNER_DIR:-/home/docker/actions-runner}
  # ✅ Option A — forwarder les credentials Docker du hôte :
  - ${DOCKER_CONFIG_HOST:-~/.docker}:/home/docker/.docker:ro
```

```bash
# .env
DOCKER_CONFIG_HOST=/root/.docker   # ou /home/votre-user/.docker
```

> Le hôte doit être préalablement authentifié : `echo "$TOKEN" | docker login ghcr.io -u user --password-stdin`. Le runner hérite alors transparairement de toutes les authentifications du daemon hôte.

### Option B — Variable `GHCR_TOKEN` (GHCR uniquement)

Ajoutez dans votre `.env` :

```bash
GHCR_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx   # PAT avec scope read:packages
GHCR_USER=your-github-username        # ou "x-token" pour auth PAT
```

L'entrypoint Phase 1 (root) exécute automatiquement `docker login ghcr.io` avec ces valeurs avant de lancer le runner.

### Images publiques

Aucune configuration requise. Le daemon hôte peut puller les images publiques sans credentials.

---

## Sécurité

> ⚠️ **Socket Docker = accès root effectif au daemon hôte**  
> Monter `/var/run/docker.sock` donne au runner (et aux workflows qu'il exécute) un accès **root effectif** au daemon Docker du hôte : lancement de containers privilégiés, montage de volumes hôte, accès réseau hôte, etc. **Réservez l'usage de ce runner à des dépôts privés et à des workflows de confiance.**

- ⚠️ **N'utilisez jamais de runners self-hosted sur des dépôts publics** — n'importe qui peut soumettre une PR exécutant du code arbitraire sur votre runner. Voir [la doc officielle](https://docs.github.com/en/actions/hosting-your-own-runners/managing-self-hosted-runners/about-self-hosted-runners#self-hosted-runner-security).
- **`EPHEMERAL=true` (défaut) est le mode recommandé** — chaque job démarre dans un container isolé, éliminant les risques de persistance de secrets entre deux jobs (cross-job secret leakage). Conforme aux recommandations de sécurité GitHub.
- Préférez un PAT **fine-grained** limité à l'organisation/dépôt cible, ou mieux : une **GitHub App**.
- Le mode éphémère limite drastiquement la persistance d'un éventuel compromis entre deux jobs.
- Stockez le token dans un secret manager (Docker secrets, Vault, AWS SSM, etc.), pas en clair dans `docker run`.

## Mettre à jour la version du runner

1. Récupérer la dernière release :

   ```bash
   curl -s https://api.github.com/repos/actions/runner/releases/latest \
     | jq -r .tag_name
   ```

2. Calculer le SHA-256 du tarball linux x64 :

   ```bash
   VERSION=2.334.0
   curl -sL "https://github.com/actions/runner/releases/download/v${VERSION}/actions-runner-linux-x64-${VERSION}.tar.gz" \
     | shasum -a 256
   ```

3. Mettre à jour `RUNNER_VERSION` et `RUNNER_SHA256` dans le `Dockerfile`.

4. Vérifier la compatibilité de `CONTAINER_HOOKS_VERSION` : [https://github.com/actions/runner-container-hooks/releases](https://github.com/actions/runner-container-hooks/releases)

5. Rebuild + push :
   ```bash
   docker build --platform linux/amd64 -t ghcr.io/abenevaut/self-hosted-github-runner:latest .
   docker push ghcr.io/abenevaut/self-hosted-github-runner:latest
   ```

Voir `CONTRIBUTING.md` pour la procédure complète de mise à jour de version.

## Pour aller plus loin : ARC

Pour gérer des runners éphémères **à l'échelle** (auto-scaling sur Kubernetes), la solution officiellement recommandée par GitHub est [**Actions Runner Controller (ARC)**](https://github.com/actions/actions-runner-controller). Cette image Docker reste pertinente pour des déploiements simples (1 à quelques runners sur un VPS, NAS Intel, machine de build, etc.).

---

## Licence

Voir le `LICENSE` à la racine du dépôt parent.

