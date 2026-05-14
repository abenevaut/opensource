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

- **`linux/amd64` uniquement** (voir bandeau ci-dessus)
- Base **Ubuntu 24.04 LTS**
- GitHub Actions Runner **2.334.0** avec vérification SHA-256 du tarball
- **Container hooks 0.8.1** — supporte les workflows utilisant `container:` / `services:` depuis l'intérieur d'un runner conteneurisé (DooD)
- Utilisateur non-root `docker` (avec `sudo` NOPASSWD)
- Enregistrement automatique au démarrage, désenregistrement automatique à l'arrêt (`SIGINT` / `SIGTERM`)
- Support **org-level** ET **repo-level**
- Mode **éphémère** activé par défaut
- Étiquettes (`labels`) et nom de runner configurables
- **Docker CLI** inclus (`docker`, `docker buildx`, `docker compose`) — accès au daemon hôte via socket passthrough (DooD)
- **Wrapper `docker`** intégré qui pré-crée les bind-mounts `_work/_temp/_github_home` etc. pour fiabiliser les jobs `container:`

## Prérequis

- Docker 20.10+
- Hôte **`linux/amd64`** (voir bandeau)
- Un **Personal Access Token** GitHub :
  - **Org-level** : scope `admin:org` (classic PAT) ou permissions `Self-hosted runners: Read & write` (fine-grained PAT, organisation entière).
  - **Repo-level** : scope `repo` (classic) ou permissions `Administration: Read & write` sur le dépôt (fine-grained).
- Le nom de l'organisation (et éventuellement du dépôt).

## Build local

```bash
cd self-hosted-github-runner
docker build --tag ghcr.io/abenevaut/self-hosted-github-runner:latest .
```

Pour builder une autre version du runner :

```bash
docker build \
  --build-arg RUNNER_VERSION=2.334.0 \
  --build-arg RUNNER_SHA256=048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271 \
  --tag ghcr.io/abenevaut/self-hosted-github-runner:2.334.0 \
  .
```

## Publication sur GHCR

Une [pipeline GitHub Actions](../.github/workflows/self-hosted-github-runner-publish.yml) (`workflow_dispatch`) build et publie l'image `linux/amd64` sur GHCR avec les tags `latest` et `YYYYMMDD`.

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

> Le volume `RUNNER_DIR:RUNNER_DIR` (même chemin host = conteneur) est **obligatoire** pour les workflows utilisant `container:` ou `services:`. Les container hooks transmettent ce chemin au daemon Docker du host qui le résout sur le filesystem hôte.

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

> Le volume `/var/run/docker.sock` passe le socket Docker du **host** dans le conteneur (pattern **DooD** — Docker-outside-of-Docker). Le runner peut ainsi exécuter `docker build`, `docker push`, `docker compose`, `docker/build-push-action`, `container:`, `services:`, etc. sans avoir à faire tourner un daemon Docker secondaire à l'intérieur du conteneur.

## Variables d'environnement

| Variable         | Obligatoire | Défaut                              | Description |
|------------------|:-----------:|-------------------------------------|-------------|
| `ORGANIZATION`   | ✅          | `<YOUR-GITHUB-ORGANIZATION>`        | Nom de l'organisation (ou owner) GitHub. |
| `ACCESS_TOKEN`   | ✅          | `<YOUR-GITHUB-ACCESS-TOKEN>`        | PAT avec scope `admin:org` (org) ou `repo` (repo). |
| `REPOSITORY`     | ❌          | *(vide)*                            | Si défini, enregistre le runner au niveau du dépôt au lieu de l'organisation. |
| `RUNNER_DIR`     | ❌          | `/home/docker/actions-runner`       | Chemin du runner sur l'hôte ET dans le conteneur (doit être identique). |
| `RUNNER_NAME`    | ❌          | `$(hostname)`                       | Nom affiché dans GitHub. |
| `RUNNER_LABELS`  | ❌          | `self-hosted,linux,x64`             | Labels (séparés par des virgules) pour cibler ce runner depuis `runs-on:`. |
| `RUNNER_WORKDIR` | ❌          | `_work`                             | Sous-dossier de travail du runner (relatif à `RUNNER_DIR`). |
| `EPHEMERAL`      | ❌          | `true`                              | Si `true`, le runner se désenregistre après un seul job. |

> Les valeurs par défaut placeholder pour `ORGANIZATION` et `ACCESS_TOKEN` provoquent un échec immédiat au démarrage si elles ne sont pas surchargées — c'est volontaire pour éviter de lancer un conteneur "muet".

## Mode éphémère

Avec `EPHEMERAL=true` (défaut), le runner exécute **un seul job** puis se désenregistre proprement et le conteneur s'arrête. Combiné à `--restart=always`, Docker en relance immédiatement un nouveau, ce qui garantit un environnement propre pour chaque job.

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

3. Mettre à jour `RUNNER_VERSION` et `RUNNER_SHA256` dans le `Dockerfile`, puis rebuild + push.

## Sécurité

- ⚠️ **N'utilisez jamais de runners self-hosted sur des dépôts publics** — n'importe qui peut soumettre une PR exécutant du code arbitraire sur votre runner. Voir [la doc officielle](https://docs.github.com/en/actions/hosting-your-own-runners/managing-self-hosted-runners/about-self-hosted-runners#self-hosted-runner-security).
- Préférez un PAT **fine-grained** limité à l'organisation/dépôt cible, ou mieux : une **GitHub App**.
- Le mode éphémère limite drastiquement la persistance d'un éventuel compromis entre deux jobs.
- Stockez le token dans un secret manager (Docker secrets, Vault, AWS SSM, etc.), pas en clair dans `docker run`.
- ⚠️ **DooD et le socket Docker** : monter `/var/run/docker.sock` donne au runner un accès **root effectif** au daemon Docker du host (il peut lancer des conteneurs privilégiés, monter des volumes host, etc.). Assurez-vous que vos workflows sont de confiance et que le host est dédié à cet usage.

## Pour aller plus loin : ARC

Pour gérer des runners éphémères **à l'échelle** (auto-scaling sur Kubernetes), la solution officiellement recommandée par GitHub est [**Actions Runner Controller (ARC)**](https://github.com/actions/actions-runner-controller). Cette image Docker reste pertinente pour des déploiements simples (1 à quelques runners sur un VPS, NAS Intel, machine de build, etc.).

---

## Licence

Voir le `LICENSE` à la racine du dépôt parent.

