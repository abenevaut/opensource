# AI Instructions - Laravel SQLMap

## 🎯 Contexte du projet
Package Laravel pour génération automatique de fichiers SQLMap via réflexion PHP.

## 🔍 Architecture technique
- **Commande principale** : `GenerateSqlMapFiles` - analyse routes et génère fichiers
- **Middleware CSRF** : `ValidateCsrfToken` - bypass pour tests SQLMap
- **Service Provider** : `SqlMapServiceProvider` - enregistrement et configuration
- **Tests** : 68 tests PHPUnit (unitaires + intégration)

## 🛠️ Composants clés
1. **Réflexion PHP** : Analyse des FormRequest et paramètres
2. **Génération HTTP** : Création de requêtes SQLMap valides
3. **Configuration** : Gestion via `.env` et `config/sqlmap.php`
4. **Sécurité** : Bypass CSRF uniquement pour User-Agent SQLMap

## ⚠️ Contraintes critiques
- **Sécurité** : Uniquement en développement/test
- **Performance** : Réflexion optimisée avec cache d'erreurs
- **Compatibilité** : Laravel 10+ et PHP 8.1+
- **Qualité** : Couverture tests > 90%

## 🎨 Conventions de code
- PSR-12 strict avec type hints
- Gestion d'erreurs avec try/catch
- Messages utilisateur avec emojis
- Documentation inline complète

## 🧪 Standards de test
- Un test par méthode publique minimum
- Tests de réflexion pour méthodes privées
- Assertions sur format HTTP exact
- Nettoyage automatique des fichiers temporaires
