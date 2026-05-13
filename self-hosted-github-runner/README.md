# self-hosted-github-runner

Image Docker minimaliste pour exécuter un [GitHub Actions self-hosted runner](https://docs.github.com/en/actions/hosting-your-own-runners) au niveau **organisation** ou **dépôt**, en mode **éphémère** par défaut (un job = un conteneur, recommandé pour la sécurité et la reproductibilité).

> Image publiée : `ghcr.io/abenevaut/self-hosted-github-runner:latest`

---

## Sommaire

- [Caractéristiques](#caractéristiques)
- [Prérequis](#prérequis)
- [Build local](#build-local)
- [Publication sur GHCR](#publication-sur-ghcr)
- [Lancer un runner](#lancer-un-runner)
  - [Au niveau organisation](#au-niveau-organisation)
  - [Au niveau dépôt](#au-niveau-dépôt)
  - [Avec docker compose](#avec-docker-compose)
- [Variables d'environnement](#variables-denvironnement)
- [Mode éphémère](#mode-éphémère)
- [Mettre à jour la version du runner](#mettre-à-jour-la-version-du-runner)
- [Sécurité](#sécurité)
- [Pour aller plus loin : ARC](#pour-aller-plus-loin--arc)

---

## Caractéristiques

- Base **Ubuntu 24.04 LTS**
- GitHub Actions Runner **2.334.0** avec vérification SHA-256 du tarball
- Utilisateur non-root `docker` (avec `sudo` NOPASSWD)
- Enregistrement automatique au démarrage, désenregistrement automatique à l'arrêt (`SIGINT` / `SIGTERM`)
- Support **org-level** ET **repo-level**
- Mode **éphémère** activé par défaut
- Étiquettes (`labels`) et nom de runner configurables

## Prérequis

- Docker 20.10+
- Un **Personal Access Token** GitHub :
  - **Org-level** : scope `admin:org` (classic PAT) ou permissions `Self-hosted runners: Read & write` (fine-grained PAT, organisation entière).
  - **Repo-level** : scope `repo` (classic) ou permissions `Administration: Read & write` sur le dépôt (fine-grained).
- Le nom de l'organisation (et éventuellement du dépôt).

## Build local

```bash
cd self-hosted-github-runner
docker build --tag ghcr.io/abenevaut/self-hosted-github-runner:latest .
```

Pour builder en **multi-architecture** (amd64 + arm64) :

```bash
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --tag ghcr.io/abenevaut/self-hosted-github-runner:latest \
  --push .
```

Pour builder une autre version du runner (les SHA256 sont distincts par architecture) :

```bash
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --build-arg RUNNER_VERSION=2.334.0 \
  --build-arg RUNNER_SHA256_AMD64=048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271 \
  --build-arg RUNNER_SHA256_ARM64=f44255bd3e80160eb25f71bc83d06ea025f6908748807a584687b3184759f7e4 \
  --tag ghcr.io/abenevaut/self-hosted-github-runner:2.334.0 \
  --push .
```

## Publication sur GHCR

```bash
echo "$GITHUB_TOKEN" | docker login ghcr.io -u abenevaut --password-stdin
docker push ghcr.io/abenevaut/self-hosted-github-runner:latest
```

## Lancer un runner

### Au niveau organisation

```bash
docker run -d --restart=always --name gh-runner \
  -e ORGANIZATION=abenevaut \
  -e ACCESS_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx \
  ghcr.io/abenevaut/self-hosted-github-runner:latest
```

### Au niveau dépôt

```bash
docker run -d --restart=always --name gh-runner-myrepo \
  -e ORGANIZATION=abenevaut \
  -e REPOSITORY=mon-repo \
  -e ACCESS_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx \
  -e RUNNER_LABELS=self-hosted,linux,x64,myrepo \
  ghcr.io/abenevaut/self-hosted-github-runner:latest
```

### Avec docker compose

Copiez le fichier d'environnement et renseignez vos valeurs :

```bash
cp .env.example .env
# éditez .env puis :
docker compose up -d
```

Le `docker-compose.yml` fourni lit les variables depuis `.env` :

```yaml
services:
  gh-runner:
    image: ghcr.io/abenevaut/self-hosted-github-runner:latest
    container_name: gh-runner
    restart: always
    environment:
      - ORGANIZATION=${ORGANIZATION:-<YOUR-GITHUB-ORGANIZATION>}
      - ACCESS_TOKEN=${ACCESS_TOKEN:-<YOUR-GITHUB-ACCESS-TOKEN>}
      # --- options (uncomment to override) ---
      # - REPOSITORY=mon-repo
      # - RUNNER_NAME=runner-01
      # - RUNNER_LABELS=self-hosted,linux,x64,docker
      # - RUNNER_WORKDIR=_work
      # - EPHEMERAL=true
```

## Variables d'environnement

| Variable         | Obligatoire | Défaut                          | Description |
|------------------|:-----------:|---------------------------------|-------------|
| `ORGANIZATION`   | ✅          | `<YOUR-GITHUB-ORGANIZATION>`    | Nom de l'organisation (ou owner) GitHub. |
| `ACCESS_TOKEN`   | ✅          | `<YOUR-GITHUB-ACCESS-TOKEN>`    | PAT avec scope `admin:org` (org) ou `repo` (repo). |
| `REPOSITORY`     | ❌          | *(vide)*                        | Si défini, enregistre le runner au niveau du dépôt au lieu de l'organisation. |
| `RUNNER_NAME`    | ❌          | `$(hostname)`                   | Nom affiché dans GitHub. |
| `RUNNER_LABELS`  | ❌          | `self-hosted,linux,x64`         | Labels (séparés par des virgules) pour cibler ce runner depuis `runs-on:`. |
| `RUNNER_WORKDIR` | ❌          | `_work`                         | Dossier de travail du runner. |
| `EPHEMERAL`      | ❌          | `true`                          | Si `true`, le runner se désenregistre après un seul job. |

> Les valeurs par défaut placeholder pour `ORGANIZATION` et `ACCESS_TOKEN` provoquent un échec immédiat au démarrage si elles ne sont pas surchargées — c'est volontaire pour éviter de lancer un conteneur "muet".

## Mode éphémère

Avec `EPHEMERAL=true` (défaut), le runner exécute **un seul job** puis se désenregistre proprement et le conteneur s'arrête. Combiné à `--restart=always`, Docker en relance immédiatement un nouveau, ce qui garantit un environnement propre pour chaque job.

Pour un runner persistant (legacy) :

```bash
docker run -d --restart=always \
  -e EPHEMERAL=false \
  -e ORGANIZATION=abenevaut \
  -e ACCESS_TOKEN=ghp_xxx \
  ghcr.io/abenevaut/self-hosted-github-runner:latest
```

## Mettre à jour la version du runner

1. Récupérer la dernière release :

   ```bash
   curl -s https://api.github.com/repos/actions/runner/releases/latest \
     | jq -r .tag_name
   ```

2. Calculer le SHA-256 des tarballs linux x64 **et** arm64 :

   ```bash
   VERSION=2.334.0

   # amd64 (x64)
   curl -sL "https://github.com/actions/runner/releases/download/v${VERSION}/actions-runner-linux-x64-${VERSION}.tar.gz" \
     | shasum -a 256

   # arm64
   curl -sL "https://github.com/actions/runner/releases/download/v${VERSION}/actions-runner-linux-arm64-${VERSION}.tar.gz" \
     | shasum -a 256
   ```

3. Mettre à jour `RUNNER_VERSION`, `RUNNER_SHA256_AMD64` et `RUNNER_SHA256_ARM64` dans le `Dockerfile`, puis rebuild + push.

## Sécurité

- ⚠️ **N'utilisez jamais de runners self-hosted sur des dépôts publics** — n'importe qui peut soumettre une PR exécutant du code arbitraire sur votre runner. Voir [la doc officielle](https://docs.github.com/en/actions/hosting-your-own-runners/managing-self-hosted-runners/about-self-hosted-runners#self-hosted-runner-security).
- Préférez un PAT **fine-grained** limité à l'organisation/dépôt cible, ou mieux : une **GitHub App**.
- Le mode éphémère limite drastiquement la persistance d'un éventuel compromis entre deux jobs.
- Stockez le token dans un secret manager (Docker secrets, Vault, AWS SSM, etc.), pas en clair dans `docker run`.

## Pour aller plus loin : ARC

Pour gérer des runners éphémères **à l'échelle** (auto-scaling sur Kubernetes), la solution officiellement recommandée par GitHub est [**Actions Runner Controller (ARC)**](https://github.com/actions/actions-runner-controller). Cette image Docker reste pertinente pour des déploiements simples (1 à quelques runners sur un VPS, NAS, machine de build, etc.).

---

## Licence

Voir le `LICENSE` à la racine du dépôt parent.

