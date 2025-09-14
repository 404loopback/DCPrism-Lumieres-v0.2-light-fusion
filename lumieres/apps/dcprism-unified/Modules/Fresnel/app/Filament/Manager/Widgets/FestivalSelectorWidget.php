<?php

namespace Modules\Fresnel\app\Filament\Manager\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;
use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidgetFactory;

/**
 * Widget FestivalSelector pour le panel Manager
 * Utilise le widget partagé avec configuration spécifique Manager
 */
class FestivalSelectorWidget extends BaseFestivalSelectorWidget
{
    /**
     * Initialisation avec configuration Manager
     */
    public function mount(): void
    {
        // Configurer pour Manager
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name', 
            'label' => 'Festival à administrer',
            'placeholder' => 'Sélectionnez un festival à administrer',
            'view_path' => 'filament.manager.widgets.festival-selector-widget',
            'should_redirect' => true
        ]);
        
        // Appeler le mount parent
        parent::mount();
    }
    
}
