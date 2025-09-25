# Normalisation du Widget FestivalSelector - DCPrism

## 🎯 Objectif

Harmoniser et partager le widget de sélection des festivals entre tous les panels, en utilisant le widget Manager comme référence (le plus complet actuellement).

## 📊 État Actuel

### Widgets existants
- ✅ **Manager** : `Modules/Fresnel/app/Filament/Manager/Widgets/FestivalSelectorWidget.php`
- ✅ **Source** : `Modules/Fresnel/app/Filament/Source/Widgets/FestivalSelectorWidget.php`
- ✅ **Shared** : `Modules/Fresnel/app/Filament/Shared/Widgets/FestivalSelectorWidget.php`

### Widget de référence (Manager)
Le widget Manager est le plus abouti avec :
- Interface utilisateur élégante
- Redirection automatique après sélection
- Intégration avec `FestivalContextService`
- Configuration flexible

## 🔧 Plan de Normalisation

### Étape 1 : Améliorer le widget Shared

**Fichier** : `Modules/Fresnel/app/Filament/Shared/Widgets/FestivalSelectorWidget.php`

```php
<?php

namespace Modules\Fresnel\app\Filament\Shared\Widgets;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Services\Context\FestivalContextService;

class FestivalSelectorWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?int $sort = 1;
    
    public ?int $selectedFestivalId = null;
    protected FestivalContextService $festivalContext;

    // Configuration par panel
    public string $sessionKey = 'selected_festival_id';
    public string $sessionNameKey = 'selected_festival_name';
    public string $label = 'Festival';
    public string $placeholder = 'Sélectionnez un festival';
    public string $viewPath = 'filament.shared.widgets.festival-selector-widget';
    public bool $shouldRedirect = false;
    public string $panelContext = 'default';

    /**
     * Configuration spécifique par panel
     */
    public function configure(array $options = []): static
    {
        $this->sessionKey = $options['session_key'] ?? $this->sessionKey;
        $this->sessionNameKey = $options['session_name_key'] ?? $this->sessionNameKey;
        $this->label = $options['label'] ?? $this->label;
        $this->placeholder = $options['placeholder'] ?? $this->placeholder;
        $this->viewPath = $options['view_path'] ?? $this->viewPath;
        $this->shouldRedirect = $options['should_redirect'] ?? $this->shouldRedirect;
        $this->panelContext = $options['panel_context'] ?? $this->panelContext;

        return $this;
    }

    public function boot(): void
    {
        $this->festivalContext = app(FestivalContextService::class);
    }

    public function mount(): void
    {
        if (!isset($this->festivalContext)) {
            $this->boot();
        }

        $this->selectedFestivalId = $this->festivalContext->getCurrentFestivalId() 
            ?: $this->getAvailableFestivals()->first()?->id;

        if ($this->selectedFestivalId && !$this->festivalContext->hasFestivalSelected()) {
            $this->festivalContext->setCurrentFestival($this->selectedFestivalId);
        }

        $this->form->fill([
            'selectedFestivalId' => $this->selectedFestivalId,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form->components([
            Select::make('selectedFestivalId')
                ->hiddenLabel()
                ->options($this->getAvailableFestivals()->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->selectedFestivalId = $state;
                    
                    if ($state) {
                        $this->festivalContext->setCurrentFestival($state);
                    } else {
                        $this->festivalContext->clearCurrentFestival();
                    }

                    $this->dispatch('festival-changed', festivalId: $state);

                    if ($this->shouldRedirect) {
                        $this->redirect(request()->header('Referer') ?: '/');
                    }
                })
                ->placeholder($this->placeholder)
                ->searchable()
                ->preload(),
        ]);
    }

    protected function getAvailableFestivals()
    {
        return $this->festivalContext->getAvailableFestivals();
    }

    public function getSelectedFestival(): ?Festival
    {
        return $this->festivalContext->getCurrentFestival();
    }

    public function getSelectedFestivalName(): string
    {
        $festival = $this->getSelectedFestival();
        return $festival ? $festival->name : 'Aucun festival sélectionné';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view($this->viewPath);
    }
}
```

### Étape 2 : Widgets spécialisés par panel

#### Manager Widget
```php
<?php

namespace Modules\Fresnel\app\Filament\Manager\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;

class FestivalSelectorWidget extends BaseFestivalSelectorWidget
{
    public function mount(): void
    {
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => '',
            'placeholder' => 'Sélectionnez un festival à administrer',
            'view_path' => 'filament.manager.widgets.festival-selector-widget',
            'should_redirect' => true,
            'panel_context' => 'manager',
        ]);

        parent::mount();
    }
}
```

#### Source Widget
```php
<?php

namespace Modules\Fresnel\app\Filament\Source\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;

class FestivalSelectorWidget extends BaseFestivalSelectorWidget
{
    public function mount(): void
    {
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => 'Festival actuel',
            'placeholder' => 'Sélectionnez votre festival',
            'view_path' => 'filament.source.widgets.festival-selector-widget',
            'should_redirect' => false,
            'panel_context' => 'source',
        ]);

        parent::mount();
    }
}
```

#### Tech Widget
```php
<?php

namespace Modules\Fresnel\app\Filament\Tech\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;

class FestivalSelectorWidget extends BaseFestivalSelectorWidget
{
    public function mount(): void
    {
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => 'Festival à valider',
            'placeholder' => 'Sélectionnez un festival',
            'view_path' => 'filament.tech.widgets.festival-selector-widget',
            'should_redirect' => false,
            'panel_context' => 'tech',
        ]);

        parent::mount();
    }
}
```

#### Cinema Widget
```php
<?php

namespace Modules\Fresnel\app\Filament\Cinema\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;

class FestivalSelectorWidget extends BaseFestivalSelectorWidget
{
    public function mount(): void
    {
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => 'Festival',
            'placeholder' => 'Sélectionnez un festival',
            'view_path' => 'filament.cinema.widgets.festival-selector-widget',
            'should_redirect' => false,
            'panel_context' => 'cinema',
        ]);

        parent::mount();
    }
}
```

### Étape 3 : Vues Blade par panel

#### Vue Manager
```blade
{{-- resources/views/filament/manager/widgets/festival-selector-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ $this->getSelectedFestivalName() }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Festival sélectionné pour administration
                </p>
            </div>
            <div class="min-w-64">
                {{ $this->form }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
```

#### Vue Source
```blade
{{-- resources/views/filament/source/widgets/festival-selector-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-2">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $this->label }}
            </h3>
            {{ $this->form }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
```

### Étape 4 : Enregistrement dans les PanelProviders

#### ManagerPanelProvider
```php
->widgets([
    Widgets\FestivalSelectorWidget::class,
    // ... autres widgets
])
```

## 🎨 Remplacement des Widgets Filament par défaut

### Désactiver les widgets par défaut

**Problème** : Filament ajoute automatiquement `FilamentInfoWidget` sur le dashboard.

**Solution** : Le remplacer dans chaque PanelProvider (garder AccountWidget) :

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->widgets([
            // ✅ Garder AccountWidget (gestion profil utilisateur)
            \Filament\Widgets\AccountWidget::class,
            
            // ✅ Nos widgets personnalisés
            Widgets\FestivalSelectorWidget::class,
            
            // ❌ FilamentInfoWidget remplacé par liens Documentation/GitHub
            // (voir options ci-dessous)
            
            // ... autres widgets existants
        ])
        ->middleware([
            // ...
        ]);
}
```

### Ajout de liens Documentation et GitHub

**Option 1** : Dans le menu de navigation
```php
->navigationGroups([
    'Ressources' => [
        'Documentation' => '/docs',
        'GitHub' => 'https://github.com/dcprism/dcprism',
    ]
])
```

**Option 2** : Dans un widget stats existant
```php
// Ajouter dans un widget existant, par exemple StatsOverview
protected function getStats(): array
{
    return [
        // ... stats existantes
        
        Stat::make('Documentation', '')
            ->description('Guide utilisateur')
            ->descriptionIcon('heroicon-o-document-text')
            ->url('/docs')
            ->color('primary'),
            
        Stat::make('GitHub', '')
            ->description('Code source')
            ->descriptionIcon('heroicon-o-code-bracket')
            ->url('https://github.com/dcprism/dcprism')
            ->color('gray'),
    ];
}
```

## 🚀 Mise en œuvre

### Commandes à exécuter

```bash
# 1. Créer les widgets manquants pour Tech et Cinema
php artisan make:filament-widget FestivalSelectorWidget --panel=tech
php artisan make:filament-widget FestivalSelectorWidget --panel=cinema

# 2. Créer les vues Blade
mkdir -p resources/views/filament/{tech,cinema}/widgets

# 3. Clear cache après modifications
php artisan filament:cache-components
php artisan view:clear
```

### Checklist de migration

- [ ] Améliorer le widget Shared (base commune)
- [ ] Mettre à jour les widgets Manager et Source existants
- [ ] Créer les widgets Tech et Cinema 
- [ ] Créer les vues Blade pour chaque panel
- [ ] Enregistrer les widgets dans les PanelProviders
- [ ] Remplacer FilamentInfoWidget (garder AccountWidget)
- [ ] Ajouter les liens Documentation/GitHub
- [ ] Tester sur tous les panels

---

**Date de création** : 23/09/2024  
**Dernière mise à jour** : 23/09/2024  
**Version** : 1.0
