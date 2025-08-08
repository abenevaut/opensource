# Laravel SQLMap Generator

Un package Laravel qui génère automatiquement des fichiers SQLMap pour tester les injections SQL sur toutes les routes de votre application Laravel en utilisant la réflexion PHP.

## 🎯 Fonctionnalités

- ✅ **Analyse automatique des routes** Laravel via réflexion
- ✅ **Détection intelligente des paramètres** (FormRequest, paramètres de route)
- ✅ **Génération de fichiers SQLMap** optimisés (.http)
- ✅ **Support multi-méthodes** (GET, POST, PUT, PATCH, DELETE)
- ✅ **Détection automatique** JSON vs Form-data
- ✅ **Bypass CSRF** configurable pour les tests
- ✅ **Script d'exécution** automatique
- ✅ **Documentation** générée automatiquement
- ✅ **Suite de tests complète** (68 tests PHPUnit)

## 🚀 Installation

```bash
composer require abenevaut/laravel-sqlmap --dev
```

Publier la configuration :

```bash
php artisan vendor:publish --tag=sqlmap-config
```

Publier le middleware CSRF (optionnel) :

```bash
php artisan vendor:publish --tag=sqlmap-middleware
```

## ⚙️ Configuration

Ajoutez dans votre `.env` :

```env
SQLMAP_ENABLED=true
SQLMAP_DISABLE_CSRF=true
SQLMAP_BASE_URL=http://127.0.0.1:8000
SQLMAP_OUTPUT_PATH=storage/sqlmap
```

Configuration avancée dans `config/sqlmap.php` :

```php
return [
    'enabled' => env('SQLMAP_ENABLED', false),
    'disable_csrf' => env('SQLMAP_DISABLE_CSRF', false),
    'base_url' => env('SQLMAP_BASE_URL', env('APP_URL')),
    'output_path' => env('SQLMAP_OUTPUT_PATH', storage_path('sqlmap')),
    
    // Routes à exclure des tests
    'skip_routes' => [
        'telescope', 'horizon', 'debugbar', '_ignition', 'livewire'
    ],
    
    // User-agents autorisés pour bypass CSRF
    'bypassed_user_agents' => [
        'sqlmap/1.8.4.7#dev (http://sqlmap.org)',
        'sqlmap/*',
    ],
    
    // Valeurs de test personnalisées
    'test_parameters' => [
        'id' => '1',
        'email' => 'test@example.com',
        'password' => 'password123',
        // ... autres valeurs
    ],
    
    // Options SQLMap par défaut
    'sqlmap_options' => [
        'level' => 3,
        'risk' => 2,
        'batch' => true,
        'flush_session' => true,
        'fresh_queries' => true,
    ],
];
```

## 🔧 Utilisation

### Génération des fichiers SQLMap

```bash
# Génération basique
php artisan sqlmap:generate

# Avec options personnalisées
php artisan sqlmap:generate --output=/path/to/output --url=http://myapp.test

# Filtrer par méthodes HTTP
php artisan sqlmap:generate --methods=GET,POST

# Inclure/exclure des routes spécifiques
php artisan sqlmap:generate --include="api/*" --exclude="admin/*"
```

### Options disponibles

| Option | Description | Exemple |
|--------|-------------|---------|
| `--output` | Dossier de sortie | `--output=storage/sqlmap` |
| `--url` | URL de base | `--url=https://myapp.com` |
| `--methods` | Méthodes HTTP | `--methods=GET,POST,PUT` |
| `--include` | Patterns à inclure | `--include="api/*,users/*"` |
| `--exclude` | Patterns à exclure | `--exclude="admin/*,telescope"` |

### Exécution des tests SQLMap

```bash
# Aller dans le dossier de sortie
cd storage/sqlmap

# Exécuter tous les tests
./run_sqlmap.sh

# Ou avec un chemin SQLMap personnalisé
./run_sqlmap.sh /path/to/sqlmap

# Ou manuellement pour un fichier spécifique
sqlmap -r get_users_index.http --batch --level=3 --risk=2
```

## 🧠 Comment ça fonctionne

### 1. Analyse des routes
Le package utilise `Route::getRoutes()` pour récupérer toutes les routes Laravel et analyse :
- Les paramètres de route (`{id}`, `{slug}`)
- Les méthodes HTTP supportées
- Les middlewares appliqués

### 2. Réflexion des contrôleurs
Pour chaque route, le package utilise la réflexion PHP pour :
- Identifier les classes `FormRequest` utilisées
- Extraire les règles de validation (`rules()`)
- Détecter les paramètres attendus

### 3. Génération intelligente
- **Routes API** → Génère des requêtes JSON
- **Routes Web** → Génère des requêtes form-data
- **Paramètres de route** → Remplacés par des valeurs de test
- **Headers SQLMap** → User-Agent SQLMap pour bypass CSRF

## 📁 Structure des fichiers générés

```
storage/sqlmap/
├── get_users_index.http          # Route GET /users
├── post_users_store.http         # Route POST /users
├── put_users_update.http         # Route PUT /users/{id}
├── post_api_users_store.http     # Route POST /api/users (JSON)
├── run_sqlmap.sh                 # Script d'exécution
└── README.md                     # Documentation
```

## 📋 Exemple de fichier généré

### Requête Form-data (route web)
```http
POST http://127.0.0.1:8000/users HTTP/1.1
Host: 127.0.0.1
Content-Type: application/x-www-form-urlencoded
Content-Length: 87
User-Agent: sqlmap/1.8.4.7#dev (http://sqlmap.org)
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Connection: keep-alive

name=Test+User&email=test%40example.com&password=password123
```

### Requête JSON (route API)
```http
POST http://127.0.0.1:8000/api/users HTTP/1.1
Host: 127.0.0.1
Content-Type: application/json
Content-Length: 87
User-Agent: sqlmap/1.8.4.7#dev (http://sqlmap.org)
Accept: application/json
Connection: keep-alive

{"name":"Test User","email":"test@example.com","password":"password123"}
```

## 🛡️ Sécurité et CSRF

### Configuration du middleware CSRF

Le package inclut un middleware CSRF personnalisé qui peut bypasser la validation pour les User-Agent SQLMap :

```php
// Dans bootstrap/app.php
$middleware->replaceInGroup(
    'web',
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \LaravelSqlMap\Http\Middleware\ValidateCsrfToken::class,
);
```

### Détection intelligente des User-Agents

Le middleware supporte :
- **Correspondance exacte** : `sqlmap/1.8.4.7#dev (http://sqlmap.org)`
- **Wildcards** : `sqlmap/*`
- **Patterns personnalisés** : Configurables dans `config/sqlmap.php`

## 🧪 Tests et Qualité

Le package dispose d'une **suite de tests complète** avec **68 tests PHPUnit** :

### Tests unitaires (22 tests)
- Tests des méthodes privées via réflexion
- Validation de la logique métier
- Tests du middleware CSRF

### Tests d'intégration (38 tests)
- Génération complète des fichiers SQLMap
- Validation du format HTTP
- Tests des options en ligne de commande

### Tests de configuration (8 tests)
- Validation du service provider
- Tests des valeurs par défaut

```bash
# Exécuter tous les tests
vendor/bin/phpunit

# Tests avec affichage détaillé
vendor/bin/phpunit --testdox

# Tests avec coverage
vendor/bin/phpunit --coverage-html coverage
```

## 🔍 Options SQLMap recommandées

```bash
sqlmap -r request.http \
    --batch \
    --level=3 \
    --risk=2 \
    --technique=BEUSTQ \
    --threads=5 \
    --flush-session \
    --fresh-queries
```

### Paramètres SQLMap expliqués

| Paramètre | Description | Valeur recommandée |
|-----------|-------------|-------------------|
| `--level` | Niveau de test (1-5) | `3` (équilibre) |
| `--risk` | Niveau de risque (1-3) | `2` (modéré) |
| `--technique` | Techniques d'injection | `BEUSTQ` (toutes) |
| `--threads` | Nombre de threads | `5` (performance) |

## 🚨 Avertissements

- ⚠️ **Ne jamais utiliser en production** - uniquement en développement/test
- ⚠️ **Désactiver CSRF uniquement pour les tests** 
- ⚠️ **Tester sur des données non sensibles**
- ⚠️ **Respecter les lois locales** sur les tests de sécurité
- ⚠️ **Configurer `SQLMAP_ENABLED=false` en production**

## 🔧 Développement

### Structure du projet
```
src/
├── SqlMapServiceProvider.php           # Service provider principal
├── Console/Commands/
│   └── GenerateSqlMapFiles.php        # Commande principale
└── Http/Middleware/
    └── ValidateCsrfToken.php          # Middleware CSRF

tests/
├── Feature/                           # Tests d'intégration
├── Unit/                             # Tests unitaires
└── TestCase.php                      # Base des tests
```

### Contribution

1. Fork le projet
2. Créer une branche : `git checkout -b feature/amazing-feature`
3. Commiter : `git commit -m 'Add amazing feature'`
4. Pousser : `git push origin feature/amazing-feature`
5. Ouvrir une Pull Request

## 📈 Roadmap

- [ ] Support pour les tests GraphQL
- [ ] Interface web pour visualiser les résultats
- [ ] Intégration CI/CD avec GitHub Actions
- [ ] Support pour d'autres outils de test (Burp Suite, OWASP ZAP)
- [ ] Templates de requêtes personnalisables

## 🤝 Contribution

Les contributions sont les bienvenues ! Ouvrez une issue ou une pull request.

### Guidelines
- Respecter les standards PSR-12
- Ajouter des tests pour les nouvelles fonctionnalités
- Maintenir la couverture de tests > 90%
- Documenter les changements dans le CHANGELOG

## 📄 Licence

MIT License - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🔗 Liens utiles

- [Documentation SQLMap](https://sqlmap.org/)
- [Guide des injections SQL OWASP](https://owasp.org/www-community/attacks/SQL_Injection)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [SQLMap Cheat Sheet](https://github.com/sqlmapproject/sqlmap/wiki)

## 🏆 Statistiques

- **68 tests PHPUnit** avec couverture complète
- **Support Laravel 10+ et 11+**
- **Compatible PHP 8.1+**
- **Génération automatique** de documentation
- **Zero configuration** - fonctionne out-of-the-box
