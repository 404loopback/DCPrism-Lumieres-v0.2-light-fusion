# Guide de Tests d'Intégration - DCPrism API

Ce guide détaille l'architecture et l'utilisation des tests d'intégration pour l'API DCPrism.

## 📋 Table des Matières

- [Vue d'ensemble](#vue-densemble)
- [Structure des tests](#structure-des-tests)
- [Configuration](#configuration)
- [Exécution des tests](#exécution-des-tests)
- [Tests par contrôleur](#tests-par-contrôleur)
- [Meilleures pratiques](#meilleures-pratiques)
- [Dépannage](#dépannage)

## 🎯 Vue d'ensemble

Les tests d'intégration de DCPrism valident le bon fonctionnement de tous les endpoints de l'API REST v1, incluant :

- **Authentification** : Login, registration, gestion des tokens
- **Movies** : CRUD, upload DCP, validation, métadonnées
- **Festivals** : Gestion des festivals, association avec films
- **Jobs** : Suivi des tâches, retry, annulation
- **DCP Processing** : Analyse, validation, upload par chunks

## 🏗️ Structure des tests

```
tests/
├── Feature/
│   └── Api/
│       └── V1/
│           ├── AuthenticationTest.php
│           ├── MovieTest.php
│           ├── FestivalTest.php
│           ├── JobTest.php
│           └── DcpProcessingTest.php
├── Unit/
└── TestCase.php
```

### Architecture des tests

Chaque classe de test suit une structure cohérente :

```php
class MovieTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['api:access', 'movies:read', 'movies:write']);
    }

    public function test_can_list_movies()
    {
        // Arrange
        Movie::factory()->count(15)->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/v1/movies');

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure(['data', 'meta']);
    }
}
```

## ⚙️ Configuration

### 1. Configuration PHPUnit

Le fichier `phpunit.xml` configure :
- Base de données SQLite en mémoire pour les tests
- Variables d'environnement spécifiques aux tests
- Suite de tests API séparée

### 2. Configuration Laravel

Dans `.env.testing` :
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

### 3. Factories et Seeders

Les factories Laravel génèrent des données de test cohérentes :
- `UserFactory` : Utilisateurs avec permissions
- `MovieFactory` : Films avec métadonnées DCP
- `FestivalFactory` : Festivals avec statuts
- `JobFactory` : Tâches avec différents états

## 🚀 Exécution des tests

### Scripts automatisés

#### Linux/macOS
```bash
./test-api.sh
```

#### Windows PowerShell
```powershell
.\test-api.ps1
```

### Commandes manuelles

#### Tous les tests API
```bash
php artisan test --testsuite=API
```

#### Tests spécifiques par contrôleur
```bash
php artisan test tests/Feature/Api/V1/AuthenticationTest.php
php artisan test tests/Feature/Api/V1/MovieTest.php
php artisan test tests/Feature/Api/V1/FestivalTest.php
php artisan test tests/Feature/Api/V1/JobTest.php
php artisan test tests/Feature/Api/V1/DcpProcessingTest.php
```

#### Tests avec options
```bash
# Arrêter au premier échec
php artisan test --stop-on-failure

# Tests avec couverture
php artisan test --coverage

# Tests en parallèle
php artisan test --parallel
```

## 🧪 Tests par contrôleur

### AuthenticationTest.php

Teste l'authentification et la gestion des utilisateurs :

- ✅ Login avec credentials valides
- ✅ Login avec credentials invalides  
- ✅ Validation des champs requis
- ✅ Rate limiting sur les tentatives de login
- ✅ Registration d'utilisateur
- ✅ Récupération des informations utilisateur
- ✅ Logout et révocation de token
- ✅ Refresh de token
- ✅ Gestion des tokens invalides
- ✅ Vérification des abilities (permissions)

### MovieTest.php

Teste la gestion des films et DCPs :

- ✅ Liste paginée des films
- ✅ Création de film avec validation
- ✅ Affichage des détails d'un film
- ✅ Mise à jour de film
- ✅ Suppression (soft delete)
- ✅ Filtrage par genre, résolution, etc.
- ✅ Recherche par titre
- ✅ Tri et pagination
- ✅ Upload de fichiers DCP
- ✅ Validation de DCP
- ✅ Extraction de métadonnées
- ✅ Génération de nomenclature
- ✅ Isolation utilisateur (sécurité)

### FestivalTest.php

Teste la gestion des festivals :

- ✅ CRUD complet des festivals
- ✅ Validation des dates (fin après début)
- ✅ Filtrage par statut et dates
- ✅ Association/dissociation de films
- ✅ Statistiques des festivals
- ✅ Traitement en lot des films
- ✅ Endpoints publics pour festivals publics
- ✅ Respect de la confidentialité
- ✅ Pagination et tri

### JobTest.php

Teste le système de jobs asynchrones :

- ✅ Liste des jobs avec filtres
- ✅ Détails d'un job
- ✅ Retry des jobs échoués
- ✅ Annulation de jobs en attente
- ✅ Logs détaillés des jobs
- ✅ Statistiques de performance
- ✅ Métriques d'exécution
- ✅ Opérations en lot (bulk cancel/retry)
- ✅ Recherche dans les payloads
- ✅ Suivi du progrès

### DcpProcessingTest.php

Teste le traitement avancé des DCPs :

- ✅ Analyse de DCP (simple/approfondie)
- ✅ Validation de DCP (niveaux multiples)
- ✅ Extraction de métadonnées techniques
- ✅ Génération de nomenclature personnalisée
- ✅ Traitement en lot
- ✅ Upload par chunks (grandes tailles)
- ✅ Suivi du progrès d'upload
- ✅ Finalisation et callbacks
- ✅ Gestion des timeouts
- ✅ Validation des tailles et contraintes

## 🎯 Meilleures pratiques

### 1. Structure AAA (Arrange-Act-Assert)

```php
public function test_can_create_movie()
{
    // Arrange - Préparer les données
    $movieData = [
        'title' => 'Test Movie',
        'duration' => 120
    ];

    // Act - Exécuter l'action
    $response = $this->postJson('/api/v1/movies', $movieData);

    // Assert - Vérifier le résultat
    $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    
    $this->assertDatabaseHas('movies', [
        'title' => 'Test Movie'
    ]);
}
```

### 2. Isolation des tests

- Chaque test doit être indépendant
- Utilisation de `RefreshDatabase` pour nettoyer la DB
- Reset des mocks entre les tests

### 3. Nommage descriptif

```php
// ✅ Bon
public function test_user_cannot_access_other_users_movies()

// ❌ Mauvais  
public function test_movies()
```

### 4. Assertions complètes

```php
$response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id', 'title', 'duration'
            ],
            'meta' => [
                'current_page', 'per_page', 'total'
            ]
        ]);
```

### 5. Test des cas d'erreur

```php
public function test_cannot_create_movie_without_title()
{
    $response = $this->postJson('/api/v1/movies', []);
    
    $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
}
```

## 🔧 Dépannage

### Problèmes courants

#### 1. Base de données

```bash
# Si les migrations échouent
php artisan migrate:fresh --env=testing

# Vérifier la configuration
php artisan config:show database --env=testing
```

#### 2. Clé d'application manquante

```bash
php artisan key:generate --env=testing
```

#### 3. Permissions de fichiers

```bash
# Linux/macOS
chmod 755 storage/
chmod -R 755 storage/logs/
chmod -R 755 storage/framework/

# Windows - Vérifier les permissions du dossier
```

#### 4. Mémoire insuffisante

Dans `phpunit.xml` :
```xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

#### 5. Timeouts sur gros uploads

```php
// Dans les tests d'upload
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');
```

### Logs de débogage

```php
// Dans un test, pour débugger
dump($response->json());
dump($response->status());

// Logs Laravel
\Log::info('Test debug', ['data' => $response->json()]);
```

### Tests en isolation

```bash
# Tester un seul test
php artisan test --filter=test_can_login_with_valid_credentials

# Tester une classe
php artisan test tests/Feature/Api/V1/AuthenticationTest.php

# Verbose pour plus de détails
php artisan test --verbose
```

## 📊 Métriques et couverture

### Couverture de code

```bash
# Génération du rapport de couverture
php artisan test --coverage-html coverage-report

# Couverture minimale requise
php artisan test --min=80
```

### Métriques ciblées

- **Couverture** : > 85% sur les contrôleurs API
- **Assertions** : Minimum 3 par test
- **Temps d'exécution** : < 30s pour la suite complète
- **Isolation** : 100% des tests indépendants

## 🚀 Intégration CI/CD

### GitHub Actions

```yaml
name: API Tests
on: [push, pull_request]

jobs:
  api-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: pdo, sqlite
          
      - name: Install Dependencies
        run: composer install --no-dev --optimize-autoloader
        
      - name: Run API Tests
        run: php artisan test --testsuite=API
```

### Badges de statut

[![API Tests](https://github.com/your-org/dcprism/workflows/API%20Tests/badge.svg)](https://github.com/your-org/dcprism/actions)

---

## 📝 Conclusion

Les tests d'intégration de DCPrism garantissent la fiabilité et la robustesse de l'API. Ils couvrent tous les scénarios d'usage, des cas normaux aux cas d'erreur, en passant par les problèmes de sécurité et de performance.

Pour toute question ou amélioration, n'hésitez pas à consulter la documentation ou ouvrir une issue sur le repository.

**Happy Testing! 🎬✨**
