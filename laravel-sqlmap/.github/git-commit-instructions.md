# Git Commit Instructions - Laravel SQLMap

## Format des commits
```
<type>(<scope>): <description>

[body optionnel]

[footer optionnel]
```

## Types spécifiques au projet
- `feat`: Nouvelle fonctionnalité SQLMap
- `fix`: Correction de génération ou tests
- `test`: Ajout/modification de tests
- `refactor`: Refactoring sans changement de comportement
- `docs`: Documentation (README, comments)
- `config`: Modification de configuration
- `security`: Corrections de sécurité CSRF/validation

## Scopes du projet
- `command`: Commande de génération
- `middleware`: Middleware CSRF
- `reflection`: Logique de réflexion PHP
- `generation`: Génération de fichiers HTTP
- `config`: Configuration et service provider
- `tests`: Tests unitaires/intégration

## Exemples
```
feat(reflection): add FormRequest parameter detection
fix(generation): correct HTTP header format for POST requests
test(middleware): add CSRF bypass validation tests
docs(readme): update installation instructions
security(middleware): validate SQLMap user agents
```

## Guidelines
- Description en anglais, impératif présent
- Corps en français si nécessaire pour expliquer le contexte
- Référencer les issues/PRs : `Fixes #123`
- Breaking changes : `BREAKING CHANGE:` in footer
