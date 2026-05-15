---
description: Spécialiste en création et maintenance de documentation projet (technique, manuel utilisateur, FAQ, CHANGELOG) à partir des artefacts spec-kit (spec.md, plan.md, tasks.md, research.md, contracts/, data-model.md). Génère et tient à jour la documentation dans un répertoire `docs/` au même niveau que `specs/`, ainsi que le `README.md` et le `CHANGELOG.md` à la racine du projet.
---

## User Input

```text
$ARGUMENTS
```

You **MUST** consider the user input before proceeding (if not empty). The input may specify:

- a target feature folder (e.g., `005-private-public-settings`) → analyser uniquement cette feature
- a documentation type (e.g., `technical`, `manual`, `faq`, `changelog`) → produire/mettre à jour seulement ce type
- a scope keyword (e.g., `all`, `incremental`, `refresh`) → mode d'agrégation
- a **namespace** (e.g., `namespace:soundcloud`, `namespace:control-panel`) → restreindre la génération aux features et fichiers appartenant à ce namespace (regroupement logique par module ou domaine fonctionnel — voir §2 Namespace resolution)
- aucune entrée → analyser **toutes** les features présentes sous `specs/` et synchroniser l'ensemble de la documentation

## Goal

Produire et maintenir, à partir de l'ensemble des artefacts spec-kit d'un projet, une documentation **vivante, cohérente et factuelle** organisée en quatre flux :

1. **Documentation technique** (`docs/technical/`) : architecture, algorithmes, modules/domaines, endpoints API, URLs publiques, commandes CLI/artisan, ports et protocoles (TCP/UDP, formats de transport et de messages), notes de sécurité.
2. **Manuel utilisateur** (`docs/manual/`) : minimaliste, action-oriented, faible volume cognitif, droit au but.
3. **FAQ vivante** (`docs/faq.md`) : questions/réponses dérivées des clarifications, edge cases, et retours utilisateurs accumulés.
4. **CHANGELOG** (`CHANGELOG.md` à la **racine du projet**) : journal des changements notables par version, au format [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Toutes les nouvelles entrées vivent sous `## [Unreleased]` jusqu'à une release explicite.
5. **README** (`README.md` à la **racine du projet**) : index du projet et porte d'entrée vers la documentation.

## Operating Constraints

- **Source de vérité** : les artefacts spec-kit (`spec.md`, `plan.md`, `tasks.md`, `research.md`, `data-model.md`, `contracts/*`, `quickstart.md`) **et** le code réel quand un fait technique doit être confirmé (routes, controllers, migrations, configs).
- **Sortie** :
  - `README.md` et `CHANGELOG.md` sont **à la racine du projet** (pas sous `docs/`).
  - Tous les autres fichiers de documentation sont sous `docs/` au même niveau que `specs/`.
  - **NE JAMAIS** écrire dans `specs/`.
- **Versionnage** :
  - Chaque fichier de documentation sous `docs/` porte en en-tête un bloc de métadonnées YAML (front matter) indiquant la version semver et la date de dernière mise à jour (voir §4 Templates).
  - **`README.md` et `CHANGELOG.md` (racine projet) ne portent QUE la date de dernière mise à jour** (champ `updated`), pas de champ `version`.
  - **La version d'un fichier de documentation ne change QUE lors d'une release** explicitement demandée par l'utilisateur (voir §6 Stratégie de release). Une simple mise à jour de contenu en cours de cycle n'incrémente PAS la version : seul le champ `updated` du front matter évolue.
  - Lors d'une release : patch (+0.0.1) pour des changements mineurs / corrections, minor (+0.1.0) pour l'ajout de sections ou de fichiers, major (+1.0.0) uniquement sur instruction explicite de l'utilisateur.
- **Idempotence** : exécuter l'agent deux fois sans changement de source ne doit produire aucun diff (hors `updated` dans le front matter).
- **Traçabilité** : chaque section générée référence la/les feature(s) source (`<!-- source: specs/005-private-public-settings/spec.md#FR-009 -->`).
- **Pas d'invention** : si une donnée manque (port, protocole, URL), inscrire `⚠️ À documenter` plutôt que d'inventer.
- **Constitution** : respecter `.specify/memory/constitution.md` du projet (style, ton, contraintes éditoriales).

## Execution Steps

### 1. Localiser le projet et ses artefacts

Depuis le répertoire courant (workspace folder déterminé par le contexte d'invocation, ex. `blog/`) :

- Vérifier l'existence de `specs/` ; si absent → abort avec message clair.
- Lister `specs/NNN-*/` (chaque dossier = une feature).
- Pour chaque feature, charger en lecture seule : `spec.md`, `plan.md`, `tasks.md`, `research.md`, `data-model.md`, `quickstart.md`, `contracts/*` (si présents).
- Charger `.specify/memory/constitution.md` si disponible.
- Si `$ARGUMENTS` cible une feature précise, restreindre l'analyse.
- Si `$ARGUMENTS` contient un **namespace** (`namespace:<value>`), appliquer la résolution de namespace (§2).

### 2. Résolution de namespace

Un **namespace** est un regroupement logique de features et de sections de documentation partageant un même périmètre fonctionnel ou technique (ex. : un module applicatif, un domaine métier, une intégration tierce). Il constitue une unité de cohérence documentaire indépendante.

- **Déclaration** : un namespace est déduit du chemin `src/<namespace>/` présent dans les chemins de fichiers de `tasks.md` ou `plan.md`, ou explicitement déclaré dans `spec.md` via un champ `namespace:`.
- **Résolution à l'invocation** : si `namespace:<value>` est fourni en argument, ne traiter que les features dont au moins un chemin de fichier dans `tasks.md` correspond au préfixe `src/<value>/` ou dont `spec.md` déclare `namespace: <value>`.
- **Impact sur la structure** : les fichiers générés sous `docs/technical/modules/` portent le nom du namespace ; les guides du manuel sous `docs/manual/guides/` sont préfixés par le namespace si plusieurs namespaces coexistent.
- **Héritage** : une feature sans namespace explicite est considérée comme appartenant au namespace `core`.

### 3. Construire l'inventaire documentaire (modèle sémantique)

Pour chaque feature (filtrée par namespace si applicable), extraire (sans inclure les artefacts bruts en sortie) :

**Faits techniques** :
- Modules / domaines impactés (déduits de `plan.md` § Project Structure et chemins de fichiers dans `tasks.md`).
- Endpoints HTTP (méthode, URL, controller, middleware) — depuis `contracts/`, `plan.md`, et confirmation par `routes/*.php` si nécessaire.
- Commandes CLI / artisan / npm (depuis `tasks.md`, `quickstart.md`, `package.json`, scripts composer).
- Ports & protocoles (depuis `compose.yml`, `Dockerfile*`, `plan.md` § Tech Stack).
- Algorithmes / règles métier non triviales (depuis `research.md` § Decisions, `data-model.md`).
- Modèles de données (entités, relations, contraintes — depuis `data-model.md`).
- Migrations notables (depuis `tasks.md` et `database/migrations/`).
- Événements / messages (queues, broadcasts, webhooks — protocoles de transport).
- Notes de sécurité : chiffrement, auth, RBAC, audit, secrets, CORS, CSP, rate limiting, validation.

**Faits utilisateur** :
- User stories (depuis `spec.md` § User Scenarios).
- Parcours principaux (happy path) et résultats observables.
- Critères de succès mesurables (`SC-###`).

**Entrées CHANGELOG** :
- Toute nouvelle fonctionnalité exposée à l'utilisateur → section `Added`.
- Toute modification de comportement existant → section `Changed`.
- Toute correction de bug documentée → section `Fixed`.
- Toute fonctionnalité supprimée → section `Removed`.
- Toute note de sécurité → section `Security`.
- Toute dépréciation → section `Deprecated`.

**FAQ candidates** :
- Questions/réponses des sessions `/speckit.clarify` (souvent listées dans `spec.md` § Clarifications).
- Edge cases documentés.
- Décisions de `research.md` (« pourquoi X plutôt que Y »).
- Limitations connues / hors-scope.

### 4. Produire la structure cible

```
<project-root>/
├── README.md                         # racine projet — index + guide de navigation
├── CHANGELOG.md                      # racine projet — journal des changements (Keep a Changelog)
├── specs/                            # source (lecture seule)
└── docs/                             # documentation générée (au même niveau que specs/)
    ├── technical/
    │   ├── README.md                 # vue d'ensemble architecture
    │   ├── architecture.md           # vue système, couches, dépendances
    │   ├── modules/
    │   │   └── <namespace>.md        # une fiche par namespace (module/domaine)
    │   ├── api/
    │   │   ├── endpoints.md          # liste exhaustive HTTP (méthode, URL, auth)
    │   │   └── urls.md               # URLs publiques (utile audit sécurité)
    │   ├── infrastructure/
    │   │   ├── ports-protocols.md    # ports TCP/UDP, services, formats msg
    │   │   └── commands.md           # commandes CLI, artisan, npm, composer
    │   ├── data-model.md             # entités agrégées toutes features
    │   └── security.md               # notes de sécurité agrégées
    ├── manual/
    │   ├── README.md                 # index manuel (1 page max)
    │   ├── getting-started.md        # 5 étapes max
    │   └── guides/
    │       └── <namespace>-<flow>.md # 1 fichier court par parcours, préfixé namespace
    └── faq.md                        # FAQ unique, sections par thème
```

> ⚠️ `README.md` et `CHANGELOG.md` vivent **à la racine du projet**, jamais dans `docs/`. Toute exécution de l'agent qui trouve un `docs/README.md` ou un `docs/CHANGELOG.md` doit le **migrer vers la racine** et supprimer l'ancien emplacement.

**Règles de placement** :
- Si une feature appartient à un namespace, l'enrichissement va dans `docs/technical/modules/<namespace>.md`.
- Si la feature ajoute des endpoints, mettre à jour `docs/technical/api/endpoints.md` et `urls.md`.
- Si la feature touche au transport (ports, queues, websockets), mettre à jour `docs/technical/infrastructure/ports-protocols.md`.
- Si la feature comporte une note sécurité (chiffrement, audit, RBAC), enrichir `docs/technical/security.md`.
- Toute feature introduit au minimum une entrée dans `CHANGELOG.md` (racine projet), sous `## [Unreleased]`.

### 5. Templates de sortie

Tous les fichiers générés **sous `docs/`** DOIVENT commencer par le front matter de versionnage suivant :

```markdown
---
version: <semver>
updated: <YYYY-MM-DD>
namespace: <namespace|all>
---
```

Les fichiers **à la racine du projet** (`README.md`, `CHANGELOG.md`) DOIVENT porter UNIQUEMENT la date de dernière mise à jour, sans champ `version` ni `namespace` :

```markdown
---
updated: <YYYY-MM-DD>
---
```

La version est lue depuis le fichier existant (si présent) et n'est **incrémentée QUE lors d'une release explicite** (cf. §6). Pour un nouveau fichier sous `docs/`, démarrer à `1.0.0`.

#### 5.1 `docs/technical/modules/<namespace>.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: <namespace>
---

# Namespace : <namespace>

> Source : agrégation des features du namespace `<namespace>`.

## Rôle
<1-3 phrases>

## Architecture interne
<schéma textuel ou bullets : entrées, sorties, dépendances>

## Algorithmes / règles métier
<liste avec source>

## Endpoints exposés
| Méthode | URL | Auth | Description | Source |
|---|---|---|---|---|

## Données persistées
<tables, colonnes notables, chiffrement, index>

## Notes de sécurité
<bullets ; chaque item référence sa feature>

## Features contributives
- `005-private-public-settings` → <ce qu'elle ajoute>
```

#### 5.2 `docs/technical/api/endpoints.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# Endpoints HTTP

> Liste exhaustive ; utile pour audit sécurité externe.

| Méthode | URL | Middleware/Auth | Controller@action | Type payload | Namespace | Source |
|---|---|---|---|---|---|---|
```

#### 5.3 `docs/technical/api/urls.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# URLs publiques du projet

> À fournir aux prestataires de sécurité (pentest, scan).

## Domaines
- `https://...` (production)
- `https://staging...` (staging)

## URLs publiques (non authentifiées)
## URLs authentifiées (utilisateur)
## URLs admin / control-panel
## URLs techniques (health, metrics, webhooks)
```

#### 5.4 `docs/technical/infrastructure/ports-protocols.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# Ports, protocoles, messages

| Service | Port | Proto (TCP/UDP) | Format de transport | Format de message | Direction | Auth | Namespace | Source |
|---|---|---|---|---|---|---|---|---|
| HTTP API | 80/443 | TCP | HTTP/1.1, HTTP/2 | JSON | inbound | Bearer/Session | core | compose.yml |
| Queue worker | — | — | Redis RESP | JSON serialized job | internal | password | core | config/queue.php |
```

#### 5.5 `docs/technical/infrastructure/commands.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# Commandes

## Artisan
| Commande | Description | Quand l'utiliser | Namespace |
|---|---|---|---|

## Composer
| Script | Effet | Namespace |
|---|---|---|

## NPM
| Script | Effet | Namespace |
|---|---|---|

## Docker / compose
| Commande | Effet | Namespace |
|---|---|---|
```

#### 5.6 `docs/technical/security.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# Notes de sécurité

## Authentification & sessions
## Autorisations (RBAC)
## Secrets & chiffrement au repos
## Validation & sanitisation
## Rate limiting & abus
## Audit & journalisation
## CORS / CSP / Headers
## Surface d'exposition (URLs publiques)
```

#### 5.7 `docs/manual/README.md`

Ton : impératif, court, scannable. Pas de paragraphes > 3 lignes. Listes à puces privilégiées.

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# Manuel utilisateur

**Objectif** : <1 phrase>

## Démarrer en 3 étapes
1. ...
2. ...
3. ...

## Que faire ensuite ?
- [<Namespace> — Parcours A](guides/<namespace>-a.md) — <1 ligne>
- [<Namespace> — Parcours B](guides/<namespace>-b.md) — <1 ligne>

## Besoin d'aide ?
→ [FAQ](../faq.md)
```

#### 5.8 `docs/manual/guides/<namespace>-<flow>.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: <namespace>
---

# <Action utilisateur>

**Quand** : <contexte>  
**Résultat** : <ce que l'utilisateur obtient>

1. ...
2. ...
3. ...

> ⚠️ <pièges éventuels en 1 ligne>
```

#### 5.9 `docs/faq.md`

```markdown
---
version: 1.0.0
updated: YYYY-MM-DD
namespace: all
---

# FAQ

> Questions tirées des clarifications, edge cases, et décisions techniques notables.

## Général
### Q: ...
**R:** ...
<!-- source: specs/<feature>/spec.md § Clarifications -->

## Sécurité
### Q: ...

## Par namespace
### <namespace>
#### Q: ...
```

#### 5.10 `CHANGELOG.md` (racine projet)

Format strict [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) + [Semantic Versioning](https://semver.org/).

Règles :
- **Emplacement** : racine du projet (`./CHANGELOG.md`), **jamais** sous `docs/`.
- Front matter minimal : seulement `updated: YYYY-MM-DD` (pas de `version`).
- Une **seule section active** nommée `## [Unreleased]` accumule **tous** les nouveaux changements tant qu'aucune release n'a été demandée par l'utilisateur. C'est l'unique zone d'écriture entre deux releases.
- Les sous-sections autorisées sous `[Unreleased]` (et sous chaque version publiée) sont : `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.
- **Une section ne peut PAS contenir deux sous-sections du même nom**. À chaque mise à jour, fusionner les nouvelles entrées dans la sous-section existante (ex. tous les `Added` finissent dans le même bloc `### Added`, dans l'ordre chronologique d'insertion).
- Les versions publiées sont triées **de la plus récente à la plus ancienne**, sous `[Unreleased]`.
- Chaque entrée de version comporte la date ISO 8601.
- Ne lister que les changements **visibles** (fonctionnalités, comportements, sécurité) — pas les tâches internes de refactoring sans impact observable.
- Chaque item référence sa feature source entre crochets (`[005]`).
- Lors d'une release (cf. §6), basculer **tout** le contenu de `[Unreleased]` sous une nouvelle section versionnée et laisser `[Unreleased]` vide (sans sous-sections, ou avec sous-sections vides).

```markdown
---
updated: YYYY-MM-DD
---

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- [005] Les champs de settings privés sont masqués dans l'interface (type password). <!-- source: specs/005-private-public-settings/spec.md#FR-010 -->
- [007] CTA Preview sur les publications non publiées. <!-- source: specs/007-.../spec.md#FR-007 -->

### Security
- [005] Les clés API tierces sont désormais chiffrées au repos. <!-- source: specs/005-private-public-settings/research.md §1 -->

## [X.Y.Z] — YYYY-MM-DD

### Added
- ...

[Unreleased]: https://github.com/<org>/<repo>/compare/vX.Y.Z...HEAD
[X.Y.Z]: https://github.com/<org>/<repo>/releases/tag/vX.Y.Z
```

#### 5.11 `README.md` (racine projet)

Front matter minimal :

```markdown
---
updated: YYYY-MM-DD
---

# <Nom du projet>

> Description courte du projet (1-3 phrases).

## Documentation

- [Documentation technique](docs/technical/README.md)
- [Manuel utilisateur](docs/manual/README.md)
- [FAQ](docs/faq.md)
- [Changelog](CHANGELOG.md)

// ...autres sections projet (build, run, contribute) — préserver le contenu existant entre <!-- manual:start --> ... <!-- manual:end -->.
```

### 6. Stratégie de mise à jour (incrémental, release, idempotence)

#### 6.1 Mise à jour incrémentale (cas par défaut)

À chaque exécution de l'agent **sans demande explicite de release** :

- **Première exécution** (aucun `docs/`, aucun `CHANGELOG.md`, aucun `README.md`) : créer la structure complète. Les fichiers sous `docs/` démarrent à la version `1.0.0`. Le `CHANGELOG.md` racine démarre avec une section `## [Unreleased]` vide (puis remplie immédiatement par les features traitées). Le `README.md` racine démarre avec sa structure minimale.
- **Mise à jour** : pour chaque fichier cible existant :
  1. Lire le front matter (version + updated, ou updated seul pour racine).
  2. Repérer les blocs marqués par commentaires source `<!-- source: specs/NNN-... -->`.
  3. Reconstruire les sections issues des features traitées dans cette passe.
  4. **Conserver** toute section/note humaine non marquée comme générée (zone `<!-- manual:start -->...<!-- manual:end -->` à respecter).
  5. Trier les entrées de tableaux par clé stable (URL, namespace, nom de module) pour minimiser les diffs.
  6. **Mettre à jour UNIQUEMENT le champ `updated`** dans le front matter. **NE PAS incrémenter `version`** sous `docs/` (la version est figée jusqu'à la prochaine release explicite).
- **CHANGELOG** : toujours ajouter les nouvelles entrées sous `## [Unreleased]`, dans la sous-section appropriée (`Added`, `Changed`, etc.). **Fusionner** dans la sous-section existante — ne jamais créer un second `### Added` ou `### Security` dans la même section.

#### 6.2 Release (sur demande explicite de l'utilisateur)

Une release est déclenchée **uniquement** lorsque l'utilisateur en fait la demande explicite (ex. : « release la doc en 1.3.0 », « publie la version 2.0.0 du changelog »).

À la release :

1. Demander/lire le numéro de version cible (semver `X.Y.Z`) et la date (par défaut : aujourd'hui).
2. Dans `CHANGELOG.md` (racine) : déplacer **tout** le contenu de `## [Unreleased]` sous une nouvelle section `## [X.Y.Z] — YYYY-MM-DD`. Laisser `## [Unreleased]` présente mais vide. Mettre à jour les liens de comparaison en bas de fichier.
3. Pour chaque fichier sous `docs/` modifié depuis la release précédente : **incrémenter la version** dans le front matter (patch / minor / major selon §Operating Constraints) et mettre à jour `updated`.
4. Mettre à jour `updated` dans `README.md` et `CHANGELOG.md` racine.
5. Ne **jamais** réécrire une version déjà publiée dans `CHANGELOG.md`.

### 7. Vérifications de cohérence avant écriture

- Toute URL listée dans `endpoints.md` doit aussi apparaître dans `urls.md` (catégorie correcte) si publique.
- Tout namespace mentionné dans une fiche doit correspondre à un chemin `src/<namespace>/` existant ou être marqué « ⚠️ planifié ».
- Toute commande citée doit être présente dans `composer.json` / `package.json` / `routes/console.php` ou marquée comme issue d'une feature non encore implémentée.
- Les notes de sécurité doivent toutes pointer une source (feature ou code).
- La FAQ ne doit pas dupliquer 1:1 le contenu d'un guide manuel (résumer + lien).
- Toute feature analysée doit produire au moins une entrée dans `CHANGELOG.md` (racine), sous `## [Unreleased]`.
- **`CHANGELOG.md`** : aucune duplication de sous-section dans une même section. Si l'agent détecte deux blocs `### Added` sous `[Unreleased]` (ou sous une même version), il doit les **fusionner** en un seul.
- **`CHANGELOG.md` et `README.md`** : présents à la racine du projet ; absence de version dans le front matter (uniquement `updated`).

### 8. Produire un rapport de synthèse

À la fin, afficher (sans écrire dans `docs/`) :

```
## Documentation Sync Report

- Mode : incremental | release (X.Y.Z)
- Features analysées : N
- Namespace(s) traité(s) : <liste>
- Fichiers créés : X
- Fichiers mis à jour : Y (champ `updated` rafraîchi ; versions incrémentées UNIQUEMENT si release)
- Fichiers inchangés : Z
- Entrées CHANGELOG ajoutées sous [Unreleased] : W
- ⚠️ Données manquantes : <liste avec emplacement à documenter>
- 🔍 Incohérences détectées : <liste (incluant sous-sections changelog dupliquées si fusion appliquée)>

### Suggestions
- ...
```

### 9. Demander confirmation pour ouvrir une PR

Proposer (sans exécuter) :

> « Souhaitez-vous que je délègue à `speckit.git.commit` la création d'un commit `docs: sync from specs (<features>) [<namespaces>]` ? »

> En mode release : `docs: release vX.Y.Z (<features>)`.

## Operating Principles

### Style éditorial

- **Documentation technique** : factuelle, dense, tableaux privilégiés, références sources systématiques. Public : développeurs, ops, prestataires sécurité.
- **Manuel utilisateur** : minimaliste, impératif, scannable. Objectif : un utilisateur trouve sa réponse en < 30 secondes. Aucune phrase de transition inutile.
- **FAQ** : Q courte, R en 1-3 phrases max, lien vers détail si besoin.
- **CHANGELOG** : voix passive neutre, orientée utilisateur final. Ne pas mentionner les détails d'implémentation interne (noms de classes, migrations SQL) sauf si directement visible par l'utilisateur ou un opérateur.

### Règles d'or

- **NE JAMAIS** inventer un port, une URL, un protocole, une commande non sourcée.
- **NE JAMAIS** modifier `specs/`.
- **NE JAMAIS** écrire `README.md` ou `CHANGELOG.md` sous `docs/` — toujours à la racine du projet.
- **NE JAMAIS** réécrire une version déjà publiée dans `CHANGELOG.md`.
- **NE JAMAIS** créer plusieurs sous-sections du même nom dans une même section de `CHANGELOG.md` (fusionner systématiquement).
- **NE JAMAIS** incrémenter la version d'un fichier `docs/` hors d'une release explicitem
