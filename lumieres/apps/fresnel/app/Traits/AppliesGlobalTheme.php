<?php

namespace App\Traits;

use Filament\Panel;

trait AppliesGlobalTheme
{
    /**
     * Applique le thème CSS global DCPrism à un panel Filament
     * Compatible avec les modes sombre et clair
     */
    protected function applyGlobalTheme(Panel $panel): Panel
    {
        return $panel
            ->viteTheme('resources/css/filament/theme.css')
            ->darkMode(); // Active le support dark/light mode
    }
}
