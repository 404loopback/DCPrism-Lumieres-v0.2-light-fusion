# Documentation Filament Shield - DCPrism

## Vue d'ensemble

DCPrism utilise **Filament Shield v4.0.2** pour gérer les rôles et permissions dans une architecture multi-panel. Shield est configuré de manière centralisée depuis le panel administrateur principal.

## Architecture Multi-Panel

### Panels disponibles

1. **Admin** (`fresnel/admin`) - Panel principal administrateur
   - Gestion complète des utilisateurs, rôles et permissions
   - Interface Shield pour la configuration des droits
   - Accès à toutes les ressources système

2. **Manager** (`fresnel/manager`) - Panel de gestion festival
   - Gestion des films, versions et DCPs par festival
   - Permissions granulaires par ressource
   - Sélection de festival via widget

3. **Tech** (`fresnel/tech`) - Panel technique  
   - Gestion technique des DCPs et films
   - Vue et édition des aspects techniques
   - Permissions limitées aux ressources techniques

4. **Source** (`fresnel/source`) - Panel fournisseur de contenu
   - Gestion des films depuis la source
   - Upload et gestion des DCPs
   - Interface simplifiée pour les fournisseurs

5. **Infrastructure** (`infrastructure`) - Panel infrastructure
   - Configuration système et infrastructure
   - Accès réservé aux administrateurs système

## Configuration Shield

### Modèle utilisateur
```php
// Modules/Fresnel/app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;
    // ...
}
```

### Configuration du plugin
```php
// Modules/Fresnel/app/Providers/Filament/FresnelAdminProvider.php
->plugins([
    FilamentShieldPlugin::make()
        ->gridColumns([
            'default' => 1,
            'sm' => 2,
            'lg' => 3
        ])
        ->sectionColumnSpan(1)
        ->checkboxListColumns([
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
        ])
        ->resourceCheckboxListColumns([
            'default' => 1,
            'sm' => 2,
        ]),
])
```

## Rôles et Permissions

### Rôles système

| Rôle | Description | Panels d'accès |
|------|-------------|----------------|
| `super_admin` | Administrateur système complet | Tous |
| `admin` | Administrateur général | Admin, Manager, Tech |
| `manager` | Gestionnaire de festival | Manager |
| `tech` | Technicien | Tech |
| `supervisor` | Superviseur | Admin, Manager |
| `source` | Fournisseur de contenu | Source |
| `cinema` | Exploitant cinéma | Cinema (à venir) |

### Permissions par panel

#### Panel Admin (fresnel)
- Gestion des utilisateurs (`view_user`, `create_user`, `edit_user`, `delete_user`)
- Gestion des films (`view_movie`, `create_movie`, `edit_movie`, `delete_movie`)
- Gestion des festivals (`view_festival`, `create_festival`, `edit_festival`)
- Shield (`view_role`, `create_role`, `edit_role`, `delete_role`)
- Et toutes les autres ressources système...

#### Panel Manager
- Films par festival (`view_any_movie::manager`, `create_movie::manager`, `edit_movie::manager`)
- Versions (`view_any_version::manager`, `edit_version::manager`)
- DCPs (`view_any_dcp::manager`)
- Nomenclatures par festival
- Paramètres de festival

#### Panel Tech
- DCPs techniques (`view_any_dcp::tech`, `edit_dcp::tech`)
- Films (lecture technique) (`view_any_movie::tech`)

#### Panel Source  
- Films source (`view_any_movie::source`)
- Upload et gestion DCPs

## Commandes utiles

### Génération des permissions

```bash
# Panel Admin (déjà généré)
docker compose exec lumieres-app php artisan shield:generate --panel=fresnel --option=all

# Panel Manager
docker compose exec lumieres-app php artisan shield:generate --resource="MovieResource,DcpResource,VersionResource" --panel=manager --option=all

# Panel Tech
docker compose exec lumieres-app php artisan shield:generate --resource="DcpResource,MovieResource" --panel=tech --option=all

# Panel Source
docker compose exec lumieres-app php artisan shield:generate --resource="MovieResource" --panel=source --option=all
```

### Gestion des rôles via Tinker

```bash
# Créer un utilisateur avec rôle
docker compose exec lumieres-app php artisan tinker
$user = User::create(['name' => 'Test', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
$user->assignRole('manager');

# Vérifier les permissions d'un utilisateur
$user->hasRole('manager');
$user->can('view_any_movie::manager');

# Lister toutes les permissions d'un rôle
Role::findByName('manager')->permissions->pluck('name');
```

### Seeders utilisateurs

Les seeders sont configurés pour créer des utilisateurs de test avec les rôles appropriés :

```php
// Modules/Fresnel/database/seeders/UsersSeeder.php
$users = [
    ['manager@dcprism.local', 'Manager Festival', 'manager'],
    ['tech@dcprism.local', 'Technicien', 'tech'],
    ['source@dcprism.local', 'Fournisseur', 'source'],
    // ...
];
```

## Troubleshooting

### Problèmes courants

1. **Permissions manquantes après ajout de ressource**
   ```bash
   docker compose exec lumieres-app php artisan shield:generate --resource="NouvelleResource" --panel=nom_panel --option=all
   ```

2. **Cache des permissions**
   ```bash
   docker compose exec lumieres-app php artisan permission:cache-reset
   docker compose exec lumieres-app php artisan filament:clear-cached-components
   ```

3. **Vérifier la configuration**
   ```bash
   docker compose exec lumieres-app php artisan shield:doctor
   ```

4. **Namespace des modules Laravel**
   - Les ressources sont dans `Modules\Fresnel\app\Filament\{Panel}\Resources`
   - Les policies sont générées dans `Modules\Fresnel\app\Policies`
   - Shield génère les permissions avec suffixe `::panel` pour différencier les panels

## Bonnes pratiques

1. **Génération des permissions** : Toujours utiliser Docker pour éviter les problèmes de permissions
2. **Nomenclature** : Les permissions suivent le format `{action}_{resource}::{panel}`
3. **Centralisation** : Gérer tous les rôles depuis le panel Admin
4. **Test** : Tester les permissions avec différents comptes utilisateur
5. **Documentation** : Maintenir à jour la liste des rôles et permissions

## ✅ **Implémentation Terminée**

Tous les panels sont maintenant protégés par le middleware `CheckPanelAccess` :

- ✅ **Admin Panel** : `'panel.access:panel.admin'`
- ✅ **Manager Panel** : `'panel.access:panel.manager'`  
- ✅ **Tech Panel** : `'panel.access:panel.tech'`
- ✅ **Source Panel** : `'panel.access:panel.source'`
- ✅ **Infrastructure Panel** : `'panel.access:panel.infrastructure'`

### **Test des permissions :**

1. **Interface Shield** : `/fresnel/admin/shield/roles`
2. **Onglet "Custom Permissions"** pour gérer l'accès aux panels
3. **Redirection automatique** vers panel autorisé si accès refusé

## Panels à venir

- **Cinema** : Panel pour les exploitants de cinéma (préparé)

---

*Documentation mise à jour : 15 septembre 2024*
*Version Shield : 4.0.2*
*Version Laravel : 12.28.1*
*Implémentation : ✅ TERMINÉE*
