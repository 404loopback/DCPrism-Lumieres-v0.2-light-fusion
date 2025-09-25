# Implémentation du Contrôle d'Accès par Festival - Resource Managers

## Vue d'ensemble

Ce document détaille l'implémentation du système de contrôle d'accès basé sur les festivals dans les **Resource Managers** de l'application DCPrism. L'objectif est de s'assurer que chaque utilisateur ne peut accéder qu'aux données des festivals qui lui sont assignés, tout en respectant la hiérarchie des rôles.

## Principe de fonctionnement

### Logique d'accès

1. **Admin/Super Admin** : Accès complet à tous les festivals, avec possibilité de filtrer par festival sélectionné
2. **Manager et autres rôles** : Accès restreint aux festivals assignés à l'utilisateur
3. **Festival sélectionné** : Filtrage supplémentaire basé sur la sélection active de l'utilisateur (`Session::get('selected_festival_id')`)

### Services utilisés

- **`FestivalAccessService`** : Service central pour appliquer les restrictions d'accès
- **`Session`** : Gestion du festival actuellement sélectionné par l'utilisateur
- **Relations Eloquent** : `$user->festivals()` pour récupérer les festivals accessibles

## Resources Manager Modifiés ✅

### 1. MovieResource Manager
**Fichier** : `Modules/Fresnel/app/Filament/Manager/Resources/MovieResource.php`

**Modifications** :
- Import de `FestivalAccessService` et `Session`
- Mise à jour de `getEloquentQuery()` avec logique d'accès par festival
- Filtrage via la relation `movies.festivals`

```php
/**
 * Filtrer les films selon les festivals accessibles à l'utilisateur et festival sélectionné
 */
public static function getEloquentQuery(): Builder
{
    $user = auth()->user();
    
    if (!$user) {
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    // Si l'utilisateur est super admin ou admin
    if ($user->hasAnyRole(['super_admin', 'admin'])) {
        $selectedFestivalId = Session::get('selected_festival_id');
        
        if (!$selectedFestivalId) {
            return parent::getEloquentQuery();
        }
        
        return parent::getEloquentQuery()
            ->whereHas('festivals', function (Builder $query) use ($selectedFestivalId) {
                $query->where('festival_id', $selectedFestivalId);
            });
    }

    // Pour les autres utilisateurs : combiner les restrictions
    $selectedFestivalId = Session::get('selected_festival_id');
    $query = FestivalAccessService::applyFestivalScope(parent::getEloquentQuery());
    
    // Filtrage supplémentaire si festival sélectionné
    if ($selectedFestivalId) {
        if ($user->festivals()->where('festivals.id', $selectedFestivalId)->exists()) {
            $query = $query->whereHas('festivals', function (Builder $subQuery) use ($selectedFestivalId) {
                $subQuery->where('festival_id', $selectedFestivalId);
            });
        } else {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
    }
    
    return $query;
}
```

### 2. DcpResource Manager
**Fichier** : `Modules/Fresnel/app/Filament/Manager/Resources/DcpResource.php`

**Modifications** :
- Import de `FestivalAccessService`
- Refactorisation complète de `getEloquentQuery()`
- Filtrage via `movie.festivals` avec conservation des relations existantes

### 3. FestivalParameterResource Manager
**Fichier** : `Modules/Fresnel/app/Filament/Manager/Resources/FestivalParameterResource.php`

**Modifications** :
- Remplacement de `modifyQueryUsing()` par `getEloquentQuery()` pour cohérence
- Filtrage direct par `festival_id` (relation directe)
- Récupération des IDs de festivals accessibles

### 4. NomenclatureResource Manager
**Fichier** : `Modules/Fresnel/app/Filament/Manager/Resources/NomenclatureResource.php`

**Modifications** :
- Amélioration de la méthode `getEloquentQuery()` existante
- Ajout de la logique de contrôle d'accès par festivals
- Conservation de la sélection automatique du festival par défaut pour les admins

## Pattern d'implémentation commun

Chaque Resource Manager suit le même pattern :

```php
public static function getEloquentQuery(): Builder
{
    $user = auth()->user();
    
    // 1. Vérification de l'authentification
    if (!$user) {
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    // 2. Logique pour admin/super_admin
    if ($user->hasAnyRole(['super_admin', 'admin'])) {
        $selectedFestivalId = Session::get('selected_festival_id');
        // ... logique admin
    }

    // 3. Logique pour utilisateurs restreints
    $selectedFestivalId = Session::get('selected_festival_id');
    $accessibleFestivalIds = $user->festivals()->pluck('festivals.id')->toArray();
    
    // 4. Application des restrictions
    // ... logique de filtrage spécifique au resource
    
    return $query;
}
```

## Resources Manager à Modifier 🔄

### Panel Admin

Les resources suivants nécessitent également une mise à jour pour appliquer la même logique :

#### 1. **UserResource** (Admin)
**Fichier** : `Modules/Fresnel/app/Filament/Admin/Resources/UserResource.php`
**Priorité** : Haute
**Raison** : Gestion des utilisateurs - restriction nécessaire selon les festivals

#### 2. **FestivalResource** (Admin)
**Fichier** : `Modules/Fresnel/app/Filament/Admin/Resources/FestivalResource.php`
**Priorité** : Moyenne
**Raison** : Admin peut voir tous les festivals, mais managers doivent être restreints

#### 3. **ParameterResource** (Admin)
**Fichier** : `Modules/Fresnel/app/Filament/Admin/Resources/ParameterResource.php`
**Priorité** : Basse
**Raison** : Paramètres globaux, mais certains utilisateurs peuvent avoir des restrictions

### Panel Source

#### 4. **MovieResource** (Source)
**Fichier** : `Modules/Fresnel/app/Filament/Source/Resources/MovieResource.php`
**Priorité** : Haute
**Raison** : Les sources ne doivent voir que leurs propres films

#### 5. **DcpResource** (Source)
**Fichier** : `Modules/Fresnel/app/Filament/Source/Resources/DcpResource.php`
**Priorité** : Haute
**Raison** : Les sources ne doivent gérer que leurs propres DCPs

### Panel Validator

#### 6. **DcpResource** (Validator)
**Fichier** : `Modules/Fresnel/app/Filament/Validator/Resources/DcpResource.php`
**Priorité** : Haute
**Raison** : Validation des DCPs selon les festivals accessibles

#### 7. **MovieResource** (Validator)
**Fichier** : `Modules/Fresnel/app/Filament/Validator/Resources/MovieResource.php`
**Priorité** : Haute
**Raison** : Validation des films selon les festivals

## Widgets et Pages Personnalisées

### À vérifier également :

1. **Dashboard Widgets** : S'assurer que les statistiques respectent les restrictions d'accès
2. **Pages personnalisées** : Vérifier les pages qui accèdent directement aux modèles
3. **Relations dans les formulaires** : Adapter les options des Select/Relationship pour respecter les accès

## Tests de Validation

### Scénarios de test à valider :

#### Pour Admin/Super Admin :
- [ ] Accès à tous les festivals si aucun festival sélectionné
- [ ] Filtrage correct si un festival est sélectionné
- [ ] Changement de festival sélectionné fonctionne

#### Pour Manager :
- [ ] Ne voit que les festivals assignés
- [ ] Filtrage par festival sélectionné fonctionne uniquement si accessible
- [ ] Erreur/vide si festival non autorisé sélectionné

#### Pour Source :
- [ ] Ne voit que ses propres contenus
- [ ] Restriction par festival si applicable

#### Pour Validator :
- [ ] Ne voit que les contenus des festivals accessibles
- [ ] Validation possible uniquement sur contenus autorisés

## Commandes de Test

```bash
# Tester l'accès aux resources avec différents utilisateurs
php artisan tinker

# Dans tinker :
$user = App\Models\User::find(1); // Admin
$user = App\Models\User::find(2); // Manager
$user = App\Models\User::find(3); // Source

# Simuler la connexion
auth()->login($user);

# Tester les requêtes
Modules\Fresnel\app\Filament\Manager\Resources\MovieResource::getEloquentQuery()->count();
```

## Prochaines Étapes

1. **Immédiat** : Modifier les Resources Source (MovieResource, DcpResource)
2. **Court terme** : Modifier les Resources Validator 
3. **Moyen terme** : Vérifier et adapter les Resources Admin
4. **Long terme** : Audit complet des widgets et pages personnalisées

## Notes Importantes

- **Performance** : Les requêtes utilisent des jointures optimisées avec `whereHas()`
- **Cache** : La session stocke le `selected_festival_id` pour éviter les requêtes répétées
- **Sécurité** : Validation systématique de l'accès au festival sélectionné
- **Compatibilité** : Conservation des relations et optimisations existantes

---

**Date de création** : 23/09/2024  
**Dernière mise à jour** : 23/09/2024  
**Version** : 1.0
