# Architecture Refactorisée - DCPrism Laravel

## 🎯 Vue d'ensemble

Cette documentation présente l'architecture refactorisée du projet DCPrism-Laravel, qui introduit des composants de base réutilisables, des patterns standardisés et des fonctionnalités avancées pour améliorer la maintenabilité, les performances et l'observabilité du système.

## 📋 Composants Refactorisés

### 1. BaseService (`app/Services/DCP/BaseService.php`)

Classe abstraite fondamentale pour tous les services DCP, offrant :

#### ✨ Fonctionnalités
- **Logging standardisé** avec contexte automatique
- **Validation intégrée** avec gestion d'erreurs
- **Système de cache intelligent** avec TTL configurable
- **Gestion d'erreurs robuste** avec sanitisation
- **Métriques de performance** intégrées

#### 📖 Utilisation
```php
class DcpAnalysisService extends BaseService implements DcpAnalysisInterface
{
    protected function getServiceName(): string
    {
        return 'dcp_analysis';
    }

    public function analyze(Movie $movie, array $options = []): AnalysisResult
    {
        return $this->executeWithLogging('DCP Analysis', function() use ($movie, $options) {
            // Logique métier avec logging automatique
            return $this->success('Analyse terminée', $analysisData);
        }, ['movie_id' => $movie->id]);
    }
}
```

### 2. Controller Amélioré (`app/Http/Controllers/Controller.php`)

Classe de base enrichie pour tous les contrôleurs API :

#### ✨ Fonctionnalités
- **Réponses API standardisées** (success, error, paginated)
- **Validation de requêtes** intégrée
- **Gestion d'exceptions** centralisée
- **Paramètres de pagination/tri** automatiques
- **Logging des requêtes** avec contexte

#### 📖 Utilisation
```php
class MovieApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'per_page' => 'integer|min:1|max:100',
            'status' => 'string|in:pending,validated'
        ]);

        $movies = Movie::paginate($validated['per_page'] ?? 15);
        
        return $this->paginatedResponse($movies);
    }
}
```

### 3. BaseApiResource (`app/Http/Resources/BaseApiResource.php`)

Classe de base pour toutes les ressources API :

#### ✨ Fonctionnalités
- **Transformation standardisée** des données
- **Formatage automatique** (dates, tailles, durées)
- **Gestion des permissions** utilisateur
- **Liens API dynamiques** 
- **Filtrage des champs** sensibles

#### 📖 Utilisation
```php
class MovieResource extends BaseApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file_size_human' => $this->formatFileSize($this->dcp_size),
            'created_at' => $this->formatDate($this->created_at),
            'can_download' => $this->userCan($request, 'download'),
            'links' => $this->generateLinks([...])
        ];
    }
}
```

### 4. Système de Traits Réutilisables

#### HasValidation (`app/Traits/HasValidation.php`)
```php
use HasValidation;

// Validation avec gestion d'erreurs
$validated = $this->validateData($data, $rules);

// Validation de fichiers
$isValid = $this->validateFile($uploadedFile, ['max:500000']);

// Obtenir les erreurs sans exception
$errors = $this->getValidationErrors($data, $rules);
```

#### HasLogging (`app/Traits/HasLogging.php`)
```php
use HasLogging;

// Logging avec contexte
$this->logInfo('Operation started', ['user_id' => 123]);
$this->logError('Operation failed', ['error' => $e->getMessage()]);

// Logging avec timing
$result = $this->logExecution('heavy_operation', function() {
    return $this->heavyProcess();
});
```

#### HasMetrics (`app/Traits/HasMetrics.php`)
```php
use HasMetrics;

// Enregistrer des métriques
$this->recordMetric('api_calls', 1, ['endpoint' => '/movies']);
$this->startTiming('database_query');
$this->endTiming('database_query');

// Métriques avec callback
$result = $this->timeExecution('operation', function() {
    return $this->process();
});
```

#### HasCaching (`app/Traits/HasCaching.php`)
```php
use HasCaching;

// Cache simple
$data = $this->cacheRemember('key', function() {
    return $this->expensiveOperation();
}, 60);

// Cache avec tags
$data = $this->cacheRememberWithTags('key', function() {
    return $this->getData();
}, ['tag1', 'tag2'], 30);
```

### 5. BaseDcpJob Amélioré (`app/Jobs/BaseDcpJob.php`)

Classe de base pour tous les jobs DCP avec fonctionnalités avancées :

#### ✨ Fonctionnalités Nouvelles
- **Retry intelligent** avec backoff exponentiel
- **Hooks personnalisables** (beforeProcess, afterProcess)
- **Métriques de performance** intégrées
- **Cache des résultats** pour optimisation
- **Estimation du temps** de completion

#### 📖 Utilisation Avancée
```php
class EnhancedDcpAnalysisJob extends BaseDcpJob
{
    public function handle(): void
    {
        // Utilise toutes les fonctionnalités avancées
        $this->handleWithEnhancements();
    }

    protected function process(): array
    {
        // Validation automatique des options
        $options = $this->validate($this->options, $rules);
        
        // Traitement avec métriques et cache
        return $this->timeExecution('analysis', function() {
            return $this->performAnalysis();
        });
    }
}
```

### 6. BaseRepository (`app/Repositories/BaseRepository.php`)

Pattern Repository complet avec fonctionnalités avancées :

#### ✨ Fonctionnalités
- **Opérations CRUD** avec cache intégré
- **Pagination et filtrage** automatiques
- **Recherche** dans champs définis
- **Gestion des transactions**
- **Métriques et logging** intégrés

#### 📖 Utilisation
```php
class MovieRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new Movie();
    }
    
    protected array $searchableFields = ['title', 'director'];
    protected array $filterableFields = ['status', 'genre', 'year'];

    // Méthodes personnalisées
    public function getPendingValidation(): Collection
    {
        return $this->cacheRememberWithTags(
            'pending_validation',
            fn() => $this->model->whereIn('status', ['upload_ok', 'in_review'])->get(),
            ['validation'],
            15
        );
    }
}
```

## 🚀 Avantages de la Refactorisation

### Réutilisabilité
- **Code DRY** strict avec composants modulaires
- **Traits réutilisables** dans tout le projet
- **Patterns standardisés** pour cohérence

### Maintenabilité
- **Code standardisé** et prévisible
- **Logging uniforme** avec contexte
- **Gestion d'erreurs** centralisée

### Performance
- **Cache intelligent** avec tags et TTL
- **Métriques détaillées** pour optimisation
- **Requêtes optimisées** (eager loading)

### Observabilité
- **Logging détaillé** avec contexte enrichi
- **Métriques système** et métier
- **Traçabilité complète** des opérations

## 📊 Métriques et Monitoring

### Types de Métriques Collectées
1. **Performance** : Temps d'exécution, mémoire, CPU
2. **Business** : Nombre d'analyses, validations, uploads
3. **Système** : Utilisation disque, requêtes DB
4. **API** : Codes de réponse, temps de réponse

### Exemple d'Utilisation des Métriques
```php
// Dans un service
$this->recordMetric('dcp_analysis', 1, [
    'status' => 'success',
    'movie_id' => $movie->id,
    'duration_ms' => $duration
]);

// Dans un job
$this->recordDcpMetrics('processing_completed', [
    'success' => true,
    'file_size' => $movie->file_size
]);
```

## 🎯 Patterns de Cache

### Stratégies de Cache Implémentées
1. **Cache par entité** : `model:{model_name}:{id}`
2. **Cache par opération** : `service:{service_name}:{operation}`
3. **Cache avec tags** : Invalidation sélective
4. **Cache avec TTL** : Expiration automatique

### Exemple de Cache avec Tags
```php
// Mise en cache avec tags
$movies = $this->cacheRememberWithTags(
    'movies_by_festival',
    fn() => $this->getMoviesByFestival($festivalId),
    ['movies', "festival:{$festivalId}"],
    60
);

// Invalidation sélective
$this->cacheFlushTags(['movies']); // Invalide tous les caches movies
```

## 🔧 Configuration et Personnalisation

### Configuration des Services
```php
// Dans un service
$service = new DcpAnalysisService();
$service->setCaching(false)          // Désactiver le cache
       ->setLogging(true)            // Activer le logging
       ->setCacheTtl(120);           // TTL de 2 heures
```

### Configuration des Repositories
```php
// Dans un repository
$repository = new MovieRepository();
$repository->setCachePrefix('movies_v2')    // Préfixe personnalisé
          ->setCacheTags(['v2', 'movies'])   // Tags personnalisés
          ->setDefaultCacheTtl(30);          // TTL par défaut
```

## 🧪 Tests et Validation

### Tests des Composants de Base
```php
class BaseServiceTest extends TestCase
{
    public function test_logging_with_context()
    {
        $service = new TestService();
        $service->logInfo('Test message', ['key' => 'value']);
        
        $this->assertLogHasContext(['key' => 'value']);
    }

    public function test_cache_functionality()
    {
        $service = new TestService();
        $result = $service->cacheRemember('test', fn() => 'cached_value');
        
        $this->assertEquals('cached_value', $result);
    }
}
```

## 📈 Métriques de Performance

### Baselines de Performance
- **Analyse DCP** : 30 secondes
- **Validation DCP** : 60 secondes
- **Extraction métadonnées** : 15 secondes
- **Génération nomenclature** : 5 secondes

### Monitoring des Déviations
```php
// Comparaison automatique avec baseline
$performanceComparison = $this->compareToBaseline('analysis', $duration);

if ($performanceComparison['status'] === 'slower') {
    $this->logWarning('Performance degradation detected', $performanceComparison);
}
```

## 🚦 Migration et Adoption

### Étapes de Migration
1. ✅ **Composants de base** créés
2. ✅ **Services existants** refactorisés
3. ✅ **Resources API** simplifiées
4. ✅ **Repositories** implémentés
5. ✅ **Jobs** améliorés
6. ✅ **Documentation** complète

### Bonnes Pratiques
- **Utiliser les traits** pour fonctionnalités communes
- **Hériter des classes de base** pour cohérence
- **Implémenter le logging** contextualisé
- **Utiliser le cache** intelligemment avec tags
- **Monitorer les performances** avec métriques

## 🔮 Évolutions Futures

### Améliorations Prévues
- **Interface de monitoring** en temps réel
- **Alertes automatiques** sur dégradation performance
- **Cache distribué** pour scalabilité
- **Métriques temps réel** avec dashboards
- **Auto-tuning** des paramètres de cache

---

*Cette architecture refactorisée pose les bases solides pour un système DCPrism maintenable, performant et observa
