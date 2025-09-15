# Plan de Nettoyage Shield - Migration Complète Sécurisée

## ✅ VÉRIFICATIONS PRÉALABLES - CONFIRMÉES

✅ **Rôles Shield actifs**: admin, cinema, manager, source, super_admin, supervisor, tech
✅ **Utilisateurs avec rôles**: 6/6 utilisateurs ont leurs rôles Shield assignés
✅ **Custom permissions**: panel.admin, panel.manager, panel.tech, panel.source, panel.cinema, panel.infrastructure
✅ **Logique multi-panels**: Fonctionne à 100% avec Shield (canAccessPanel utilise hasRole())
✅ **Protection des panels**: Tous les PanelProvider utilisent 'panel.access:panel.xxx'

## 🔥 PHASE 1 : SUPPRESSION COLONNE LEGACY (PRIORITÉ HAUTE)

### 1.1 Supprimer 'role' du fillable User
```php
// Modules/Fresnel/app/Models/User.php - Ligne 31
protected $fillable = [
    'name', 'email', 'password',
    // 'role', ❌ SUPPRIMER CETTE LIGNE
    'is_active', 'last_login_at',
];
```

### 1.2 Migration pour supprimer la colonne
```bash
docker compose exec lumieres-app php artisan make:migration remove_role_column_from_users_table
```

## 🔧 PHASE 2 : CORRECTION DES MIDDLEWARES

### 2.1 Corriger RedirectByRole.php
```php
// Modules/Fresnel/app/Http/Middleware/RedirectByRole.php - Ligne 28
// REMPLACER :
$authorizedPanel = $rolePanelMapping[$user->role] ?? null;

// PAR :
$userRole = $user->roles->first()?->name;
$authorizedPanel = $rolePanelMapping[$userRole] ?? null;
```

### 2.2 Supprimer FilamentRoleRedirect.php (DEBUG)
```bash
rm Modules/Fresnel/app/Http/Middleware/FilamentRoleRedirect.php
```

## 🎯 PHASE 3 : CORRECTION MOVIERESOURCE

### 3.1 Remplacer logique legacy par Shield
```php
// Modules/Fresnel/app/Filament/Manager/Resources/MovieResource.php
// LIGNES 406-407 ET 420

// REMPLACER :
if ($existingUser->role !== 'source') {
    $existingUser->update(['role' => 'source']);
}

// PAR :
if (!$existingUser->hasRole('source')) {
    $existingUser->assignRole('source');
}

// ET LIGNE 420 :
$user = User::create([...]);
$user->assignRole('source'); // Au lieu de 'role' => 'source'
```

## 🗑️ PHASE 4 : SUPPRESSION DES DOUBLONS

### 4.1 Supprimer modèles Role legacy
```bash
rm app/Models/Role.php
rm Modules/Fresnel/app/Models/Role.php
```

### 4.2 Nettoyer UserSeeder.php
```php
// database/seeders/UserSeeder.php
// Supprimer toutes les lignes 'role' => 'xxx'
// Garder seulement 'shield_role' => 'xxx'
```

## 🔐 PHASE 5 : INTÉGRATION TELESCOPE

### 5.1 Ajouter permission Telescope à Shield
```php
// config/filament-shield.php
'custom_permissions' => [
    // ... existantes
    'telescope.view' => 'View Telescope Dashboard',
],
```

### 5.2 Corriger TelescopeServiceProvider
```php
// Modules/Meniscus/app/Providers/TelescopeServiceProvider.php
protected function gate(): void
{
    Gate::define('viewTelescope', function ($user) {
        return $user->can('telescope.view');
    });
}
```

## 📊 TESTS DE VALIDATION

### Test 1 : Accès aux panels
```bash
# Tester l'accès avec différents utilisateurs
curl -u admin@dcprism.local:admin123 http://localhost/fresnel/admin
curl -u manager@dcprism.local:password http://localhost/fresnel/manager
```

### Test 2 : Vérification rôles
```bash
docker compose exec lumieres-app php artisan tinker --execute="
\$user = Modules\Fresnel\app\Models\User::where('email', 'manager@dcprism.local')->first();
echo \$user->can('panel.manager') ? 'OK' : 'FAIL';
"
```

## ⚠️ POINTS D'ATTENTION

### ✅ CE QUI NE CASSERA PAS :
1. **Custom permissions panels** → Restent identiques
2. **Méthode canAccessPanel()** → Utilise déjà hasRole()
3. **Protection des panels** → Middlewares 'panel.access:panel.xxx' inchangés
4. **Interface Shield** → Gestion centralisée préservée
5. **Rôles assignés** → Tous les utilisateurs ont leurs rôles Shield

### ⚠️ CE QUI SERA CORRIGÉ :
1. **Incohérences** → Plus de duplication entre legacy et Shield
2. **Middlewares défaillants** → RedirectByRole utilisera Shield
3. **Création d'utilisateurs** → MovieResource utilisera assignRole()
4. **Code legacy** → Suppression des vestiges non utilisés

## 🎯 ORDRE D'EXÉCUTION RECOMMANDÉ

1. **PHASE 1** → Critique (sécurité)
2. **PHASE 3** → Important (fonctionnalité création users)
3. **PHASE 2** → Moyen (middlewares)
4. **PHASE 4** → Maintenance (nettoyage)
5. **PHASE 5** → Optionnel (Telescope)

## 🚀 RÉSULTAT FINAL

Après nettoyage :
- ✅ **100% Shield** → Plus de système legacy
- ✅ **Multi-panels intact** → Logique préservée et renforcée
- ✅ **Code propre** → Plus de duplication
- ✅ **Sécurité renforcée** → Un seul système de permissions
- ✅ **Maintenance simplifiée** → Une seule source de vérité
