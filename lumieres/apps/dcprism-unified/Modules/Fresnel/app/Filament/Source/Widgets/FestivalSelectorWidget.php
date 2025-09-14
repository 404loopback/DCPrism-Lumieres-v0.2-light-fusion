<?php

namespace Modules\Fresnel\app\Filament\Source\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;
use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidgetFactory;

/**
 * Widget FestivalSelector pour le panel Source
 * Utilise le widget partagé avec configuration spécifique Source
 */
class FestivalSelectorWidget extends BaseFestivalSelectorWidget
{
    /**
     * Initialisation avec configuration Source
     */
    public function mount(): void
    {
        // Configurer pour Source
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => 'Festival actuel',
            'placeholder' => 'Sélectionnez un festival',
            'view_path' => 'filament.source.widgets.festival-selector-widget',
            'should_redirect' => false
        ]);
        
        // Appeler le mount parent
        parent::mount();
    }
    
}
