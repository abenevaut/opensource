# GitHub Copilot Instructions - Laravel SQLMap

## Contexte du projet
Ce package Laravel génère automatiquement des fichiers SQLMap pour tester les injections SQL en utilisant la réflexion PHP.

## Objectifs
- Analyser les routes Laravel via réflexion
- Générer des fichiers HTTP pour SQLMap
- Gérer les FormRequest et paramètres
- Maintenir une couverture de tests > 90%

## Guidelines spécifiques

### Code Style
- PSR-12 strict
- Type hints obligatoires
- DocBlocks complets
- Gestion d'erreurs robuste

### Sécurité
- CSRF bypass uniquement en développement
- Validation des User-Agents SQLMap
- Pas de données sensibles dans les tests
- Configuration via environnement

### Tests
- Tests unitaires pour méthodes privées (réflexion)
- Tests d'intégration complets
- Mocks pour les dépendances externes
- Assertions détaillées sur le format HTTP

### Architecture
- Command pattern pour la génération
- Service Provider pour l'enregistrement
- Middleware pour CSRF bypass
- Configuration centralisée

## Patterns à éviter
- Hardcoded values (utiliser la config)
- Tests sans assertions
- Code sans gestion d'erreurs
- Méthodes trop complexes (> 20 lignes)

## Patterns à favoriser
- Factory pattern pour la génération
- Strategy pattern pour différents formats
- Builder pattern pour les requêtes HTTP
- Repository pattern pour la configuration
