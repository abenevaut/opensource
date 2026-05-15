# Specification Quality Checklist: Self-Hosted GitHub Actions Runner

**Purpose**: Validate specification completeness and quality before proceeding to planning
**Created**: 2026-03-19
**Feature**: [spec.md](../spec.md)

## Content Quality

- [x] No implementation details (languages, frameworks, APIs)
  - Note: socket Docker, DooD, `/var/run/docker.sock`, `RUNNER_DIR`, `EPHEMERAL` apparaissent — ce sont des **contraintes structurelles externes** du domaine GitHub Actions / Docker (pas des choix d'implémentation internes au projet), donc considérés acceptables dans la spec. Le choix de la base d'image, du langage des scripts, de l'outil de build, etc. reste hors spec.
- [x] Focused on user value and business needs
- [x] Written for non-technical stakeholders
  - Caveat : le domaine est intrinsèquement infra/devops, mais la spec privilégie le « WHAT » (runner Idle, jobs réussis, désenregistrement propre, fail fast) plutôt que le « HOW ».
- [x] All mandatory sections completed (User Scenarios, Requirements, Success Criteria)

## Requirement Completeness

- [ ] No [NEEDS CLARIFICATION] markers remain
  - **2 markers restants** (sous la limite de 3) :
    - FR-050 (et Edge Cases ephemeral) : valeur par défaut de `EPHEMERAL`
    - FR-063 : auto-update du binaire runner dans le scope ou non
    - Edge Cases (réseau) : stratégie de retry au démarrage
  - Total réel : **3 markers**, à résoudre dans la suite (`/speckit.clarify`).
- [x] Requirements are testable and unambiguous
- [x] Success criteria are measurable
- [x] Success criteria are technology-agnostic (no implementation details)
  - Vérifié : SC-001..SC-009 mesurent des temps, des taux d'erreur, du poids d'image, du nombre de runners zombies — pas de framework/langage.
- [x] All acceptance scenarios are defined (Given/When/Then pour chaque User Story)
- [x] Edge cases are identified (section dédiée + cas embarqués dans les user stories Synology / DooD)
- [x] Scope is clearly bounded (section "Out of Scope" explicite)
- [x] Dependencies and assumptions identified (section Assumptions)

## Feature Readiness

- [x] All functional requirements have clear acceptance criteria (couverts par les Acceptance Scenarios des User Stories ou par les Success Criteria)
- [x] User scenarios cover primary flows (lancement, exécution `container:`/`services:`, Synology, configuration, publication d'image)
- [x] Feature meets measurable outcomes defined in Success Criteria
- [x] No implementation details leak into specification (les détails d'impl. — entrypoint en deux phases, wrapper docker, choix de base Ubuntu, etc. — restent dans le code/README et ne sont **pas** prescrits par la spec)

## Notes

- Les 3 marqueurs `[NEEDS CLARIFICATION]` portent sur des décisions de scope/UX et seront tranchés via `/speckit.clarify` (ou directement par l'équipe avant `/speckit.plan`).
- La spec est volontairement une **remise à plat** : aucun élément du code actuel (entrypoint, wrapper, compose) n'est repris comme exigence — seuls les comportements externes observables le sont.
- Le hook `before_specify` (git) est désactivé dans `.specify/extensions.yml` ; la branche `001-self-hosted-runner` a été créée manuellement à partir de `chore/docker-runner`.
- Items marked incomplete require spec updates before `/speckit.clarify` or `/speckit.plan`.

