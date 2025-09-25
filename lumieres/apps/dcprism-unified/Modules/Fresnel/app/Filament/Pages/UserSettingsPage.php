<?php

namespace Modules\Fresnel\app\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class UserSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Paramètres Utilisateur';

    protected static ?string $title = 'Mes Paramètres';

    protected static ?int $navigationSort = 999;

    protected static string|UnitEnum|null $navigationGroup = 'Mon Compte';

    protected string $view = 'filament.pages.user-settings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        // Initialisation des données utilisateur
        // Formulaire à implémenter plus tard
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'userRoles' => auth()->user()?->roles->pluck('name')->toArray() ?? [],
            'userFestivals' => auth()->user()?->festivals ?? collect(),
        ];
    }
}
