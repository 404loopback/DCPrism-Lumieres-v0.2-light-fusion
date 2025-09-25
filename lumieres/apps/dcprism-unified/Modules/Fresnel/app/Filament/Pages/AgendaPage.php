<?php

namespace Modules\Fresnel\app\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class AgendaPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $title = 'Mon Agenda';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Planification';

    protected string $view = 'filament.pages.agenda';

    public string $activeView = 'calendar';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        // Récupération des événements de l'utilisateur
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'userFestivals' => auth()->user()?->festivals ?? collect(),
            'upcomingEvents' => [], // À implémenter
            'activeView' => $this->activeView,
        ];
    }

    public function changeView(string $view): void
    {
        $this->activeView = $view;
    }

    public function getUpcomingDeadlines(): array
    {
        // À implémenter : récupérer les deadlines selon le rôle
        return [];
    }
}
