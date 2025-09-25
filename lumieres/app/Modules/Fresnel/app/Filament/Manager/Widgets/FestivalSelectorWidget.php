<?php

namespace Modules\Fresnel\app\Filament\Manager\Widgets;

use Modules\Fresnel\app\Filament\Shared\Widgets\FestivalSelectorWidget as BaseFestivalSelectorWidget;

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
        // Configurer pour Manager (sans label pour éviter "Festival actuel:")
        $this->configure([
            'session_key' => 'selected_festival_id',
            'session_name_key' => 'selected_festival_name',
            'label' => '', // Pas de label
            'placeholder' => 'Sélectionnez un festival à administrer',
            'view_path' => 'filament.manager.widgets.festival-selector-widget',
            'should_redirect' => true,
        ]);

        // Appeler le mount parent
        parent::mount();
    }
}
