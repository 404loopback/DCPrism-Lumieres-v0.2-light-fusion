# 🎨 Theme CSS Custom pour Modales Filament - DCPrism

## 📋 Vue d'ensemble

Ce document explique l'implémentation du système de thème CSS personnalisé pour les modales Filament dans DCPrism. Cette solution permet d'ajouter des styles custom aux modales sans casser le thème par défaut de Filament.

## 🎯 Problème résolu

Par défaut, les modales Filament n'héritent pas des styles Tailwind CSS du projet. L'utilisation de `->viteTheme()` remplace complètement le thème Filament, cassant toute l'interface.

**❌ Ce qui ne marche PAS :**
```php
// NE PAS FAIRE : Casse complètement Filament
$panel->viteTheme('resources/css/filament/theme.css')
```

**✅ Ce qui marche :**
```php
// Utiliser FilamentAsset pour ajouter du CSS PAR-DESSUS le thème par défaut
FilamentAsset::register([
    \Filament\Support\Assets\Css::make('custom-css', asset('css/modals-fix.css')),
]);
```

## 🏗️ Architecture de la solution

### 1. Fichier CSS personnalisé

**Emplacement :** `/public/css/modals-fix.css`

Ce fichier contient des classes CSS spécialisées pour les cartes de statistiques dans les modales :

```css
/* Cartes de statistiques */
.fi-modal-content .stat-card-primary { /* Bleu - Informations générales */ }
.fi-modal-content .stat-card-success { /* Vert - Éléments validés */ }
.fi-modal-content .stat-card-warning { /* Jaune - Avertissements */ }
.fi-modal-content .stat-card-danger  { /* Rouge - Erreurs/Problèmes */ }
.fi-modal-content .stat-card-info    { /* Bleu info - En cours */ }
.fi-modal-content .stat-card-gray    { /* Gris - Neutres */ }

/* Éléments des cartes */
.fi-modal-content .stat-icon         { /* Conteneur d'icône */ }
.fi-modal-content .stat-icon-bg      { /* Background d'icône */ }
.fi-modal-content .stat-number       { /* Chiffres/Données principales */ }
.fi-modal-content .stat-label        { /* Labels/Descriptions */ }
```

### 2. Enregistrement du CSS

**Dans chaque PanelProvider concerné** (ex: `ManagerPanelProvider.php`) :

```php
<?php

namespace App\Providers\Filament;

use Filament\Support\Facades\FilamentAsset;
use Filament\PanelProvider;

class ManagerPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentAsset::register([
            \Filament\Support\Assets\Css::make('modals-fix', asset('css/modals-fix.css')),
        ]);
    }
    
    public function panel(Panel $panel): Panel
    {
        // Configuration du panel...
    }
}
```

### 3. Utilisation dans les vues Blade

**Structure HTML recommandée :**

```html
<div class="stat-card-success">
    <div class="stat-icon">
        <div class="stat-icon-bg">
            <x-filament::icon icon="heroicon-s-check-circle" class="h-5 w-5" />
        </div>
    </div>
    <div class="stat-number">{{ $stats['valid'] }}</div>
    <div class="stat-label">DCPs Validés</div>
</div>
```

## 📁 Structure des fichiers

```
DCPrism-Laravel/
├── public/css/modals-fix.css                          # CSS personnalisé
├── app/Providers/Filament/ManagerPanelProvider.php    # Enregistrement du CSS
├── resources/views/filament/modals/
│   ├── dcp-stats.blade.php                           # Modale statistiques DCP
│   └── version-nomenclature-preview.blade.php        # Modale aperçu versions
└── README-FILAMENT-THEME.md                           # Cette documentation
```

## 🎨 Classes CSS disponibles

### Cartes de statistiques

| Classe | Couleur | Usage recommandé |
|--------|---------|------------------|
| `.stat-card-primary` | Bleu | Données générales, totaux |
| `.stat-card-success` | Vert | Éléments validés, réussites |
| `.stat-card-warning` | Jaune | Avertissements, en attente |
| `.stat-card-danger` | Rouge | Erreurs, problèmes |
| `.stat-card-info` | Bleu info | Informations, en cours |
| `.stat-card-gray` | Gris | Données neutres, archivées |

### Éléments des cartes

| Classe | Description |
|--------|-------------|
| `.stat-icon` | Container flex centré pour l'icône |
| `.stat-icon-bg` | Background circulaire de l'icône |
| `.stat-number` | Nombre/donnée principale (1.5rem, bold) |
| `.stat-label` | Label/description (0.75rem, medium) |

## 🖥️ Exemples d'usage

### 1. Carte simple avec chiffre

```html
<div class="stat-card-primary">
    <div class="stat-icon">
        <div class="stat-icon-bg">
            <x-filament::icon icon="heroicon-s-film" class="h-5 w-5" />
        </div>
    </div>
    <div class="stat-number">{{ $stats['total'] }}</div>
    <div class="stat-label">Total DCPs</div>
</div>
```

### 2. Carte avec texte personnalisé

```html
<div class="stat-card-success">
    <div class="stat-icon">
        <div class="stat-icon-bg">
            <x-filament::icon icon="heroicon-s-document-text" class="h-5 w-5" />
        </div>
    </div>
    <div class="stat-label">Nomenclature générée</div>
    <div class="stat-number" style="font-family: monospace; font-size: 1rem !important;">
        {{ $nomenclature }}
    </div>
</div>
```

### 3. Grille de cartes

```html
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
    <div class="stat-card-success">...</div>
    <div class="stat-card-warning">...</div>
    <div class="stat-card-danger">...</div>
    <div class="stat-card-info">...</div>
</div>
```

## 🔧 Configuration dans les ressources

### Exemple dans DcpResource (Manager)

```php
Action::make('stats')
    ->label('Statistiques')
    ->icon('heroicon-o-chart-bar')
    ->color('info')
    ->modalHeading('Statistiques des DCPs')
    ->modalContent(view('filament.modals.dcp-stats', $this->getDcpStatsData()))
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Fermer')
```

### Exemple dans VersionResource (Manager)

```php
Action::make('preview_nomenclature')
    ->label('Aperçu Nomenclature')
    ->icon('heroicon-o-eye')
    ->color('info')
    ->modalHeading('Aperçu des nomenclatures')
    ->modalContent(function (Version $record) {
        return static::getNomenclaturePreview($record);
    })
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Fermer')
```

## ⚙️ Installation et configuration

### 1. Vérifier la présence du CSS

```bash
# Le fichier doit exister
ls -la public/css/modals-fix.css
```

### 2. Vérifier l'enregistrement dans le PanelProvider

```php
// Dans app/Providers/Filament/ManagerPanelProvider.php
public function boot(): void
{
    FilamentAsset::register([
        \Filament\Support\Assets\Css::make('modals-fix', asset('css/modals-fix.css')),
    ]);
}
```

### 3. Tester l'application des styles

Ajoutez temporairement ce CSS de test :

```css
/* Test - à supprimer après validation */
.fi-modal {
    border: 3px solid red !important;
}
```

Si vous voyez une bordure rouge sur les modales → Le CSS se charge ✅

### 4. Vider les caches

```bash
php artisan view:clear
php artisan cache:clear
```

## 🐛 Dépannage

### Le CSS ne se charge pas

1. **Vérifiez le chemin du fichier :**
   ```bash
   ls -la public/css/modals-fix.css
   ```

2. **Vérifiez l'enregistrement dans le PanelProvider :**
   ```php
   // Le boot() est-il bien appelé ?
   public function boot(): void { ... }
   ```

3. **Vérifiez les permissions du fichier :**
   ```bash
   chmod 644 public/css/modals-fix.css
   ```

### Les styles ne s'appliquent pas

1. **Vérifiez la spécificité CSS :**
   ```css
   /* Utilisez !important si nécessaire */
   .fi-modal-content .stat-card-primary {
       background: rgb(238 242 255) !important;
   }
   ```

2. **Vérifiez la structure HTML :**
   ```html
   <!-- La classe doit être dans .fi-modal-content -->
   <div class="stat-card-primary">...</div>
   ```

### Mode sombre non fonctionnel

Les styles actuels sont optimisés pour le mode clair. Pour le mode sombre, ajoutez :

```css
@media (prefers-color-scheme: dark) {
    .fi-modal-content .stat-card-primary {
        background: rgb(30 27 75) !important;
        border-color: rgb(67 56 202) !important;
        color: rgb(165 180 252) !important;
    }
}
```

## 🔄 Évolutions futures

### Amélirations possibles

1. **Support complet du mode sombre**
2. **Animations CSS pour les transitions**
3. **Plus de variantes de cartes (sizes, layouts)**
4. **Système de thème complet avec variables CSS**

### Migration vers un thème complet

Si vous souhaitez créer un thème Filament complet :

1. Créer un thème avec `php artisan make:filament-theme`
2. Migrer les styles vers le nouveau thème
3. Utiliser `->theme()` au lieu de `FilamentAsset::register()`

## 📚 Références

- [Documentation Filament Assets](https://filamentphp.com/docs/support/assets)
- [Documentation Filament Theming](https://filamentphp.com/docs/panels/themes)
- [Tailwind CSS Colors](https://tailwindcss.com/docs/customizing-colors)

---

**Créé pour DCPrism** - Système de gestion de contenus cinématographiques
**Dernière mise à jour :** Janvier 2025
