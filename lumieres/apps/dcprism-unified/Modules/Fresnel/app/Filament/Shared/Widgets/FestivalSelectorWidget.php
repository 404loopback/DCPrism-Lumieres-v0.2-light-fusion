<?php

namespace Modules\Fresnel\app\Filament\Shared\Widgets;

use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Services\Context\FestivalContextService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Widget FestivalSelector partagé
 * Remplace les duplications dans Manager/ et Source/
 */
class FestivalSelectorWidget extends Widget implements HasForms
{
    use InteractsWithForms;
    
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];
    
    protected static ?int $sort = 1;
    
    public ?int $selectedFestivalId = null;
    
    protected FestivalContextService $festivalContext;
    
    public function boot(): void
    {
        $this->festivalContext = app(FestivalContextService::class);
    }
    
    // Configuration par panel
    protected string $sessionKey = 'selected_festival_id';
    protected string $sessionNameKey = 'selected_festival_name';
    protected string $label = 'Festival';
    protected string $placeholder = 'Sélectionnez un festival';
    protected string $viewPath = 'filament.shared.widgets.festival-selector-widget';
    protected bool $shouldRedirect = false;
    
    /**
     * Configuration pour les différents panels
     */
    public function configure(array $options = []): static
    {
        $this->sessionKey = $options['session_key'] ?? $this->sessionKey;
        $this->sessionNameKey = $options['session_name_key'] ?? $this->sessionNameKey;
        $this->label = $options['label'] ?? $this->label;
        $this->placeholder = $options['placeholder'] ?? $this->placeholder;
        $this->viewPath = $options['view_path'] ?? $this->viewPath;
        $this->shouldRedirect = $options['should_redirect'] ?? $this->shouldRedirect;
        
        return $this;
    }
    
    public function mount(): void
    {
        // Ensure dependency is available
        if (!isset($this->festivalContext)) {
            $this->boot();
        }
        
        // Récupérer le festival sélectionné depuis le service
        $this->selectedFestivalId = $this->festivalContext->getCurrentFestivalId() ?: $this->getAvailableFestivals()->first()?->id;
        
        // Sauvegarder dans le contexte si nécessaire
        if ($this->selectedFestivalId && !$this->festivalContext->hasFestivalSelected()) {
            $this->festivalContext->setCurrentFestival($this->selectedFestivalId);
        }
        
        // Remplir le formulaire
        $this->form->fill([
            'selectedFestivalId' => $this->selectedFestivalId
        ]);
    }
    
    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Select::make('selectedFestivalId')
                    ->label($this->label)
                    ->options($this->getAvailableFestivals()->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedFestivalId = $state;
                        
                        // Utiliser le service de contexte
                        if ($state) {
                            $this->festivalContext->setCurrentFestival($state);
                        } else {
                            $this->festivalContext->clearCurrentFestival();
                        }
                        
                        // Déclencher événement pour autres widgets
                        $this->dispatch('festival-changed', festivalId: $state);
                        
                        // Redirection optionnelle (pour Manager)
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

/**
 * Factory methods pour les différents panels
 */
class FestivalSelectorWidgetFactory
{
    public static function forManager(): array
    {
        return [
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name', 
            'label' => 'Festival à administrer',
            'placeholder' => 'Sélectionnez un festival à administrer',
            'view_path' => 'filament.manager.widgets.festival-selector-widget',
            'should_redirect' => true
        ];
    }
    
    public static function forSource(): array
    {
        return [
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => 'Festival actuel',
            'placeholder' => 'Sélectionnez un festival',
            'view_path' => 'filament.source.widgets.festival-selector-widget',
            'should_redirect' => false
        ];
    }
    
    public static function forTech(): array
    {
        return [
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => 'Festival à valider',
            'placeholder' => 'Sélectionnez un festival',
            'view_path' => 'filament.tech.widgets.festival-selector-widget',
            'should_redirect' => false
        ];
    }
}
