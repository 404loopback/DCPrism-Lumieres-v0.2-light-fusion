<?php

namespace Modules\Fresnel\app\Filament\Shared\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\Festival;
use UnitEnum;

class IncomingFestivalsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Festivals à venir';

    protected static ?string $title = 'Festivals à venir';

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Planification';

    protected string $view = 'filament.pages.shared.incoming-festivals';

    public string $activeView = 'timeline';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasAnyRole([
            'source', 'manager', 'tech', 'cinema', 'admin', 'super_admin'
        ]);
    }

    public function mount(): void
    {
        // Initialisation des données festivals
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'upcomingFestivals' => $this->getUpcomingFestivals(),
            'activeView' => $this->activeView,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes festival avec dates
            ])
            ->filters([
                // À implémenter : filtres par date, statut
            ])
            ->actions([
                // À implémenter : actions selon le rôle
            ]);
    }

    protected function getTableQuery()
    {
        $user = auth()->user();

        // Pour admin/super_admin : tous les festivals à venir
        if ($user?->hasRole(['admin', 'super_admin'])) {
            return Festival::where('start_date', '>=', now())->orderBy('start_date');
        }

        // Pour les autres : festivals liés à l'utilisateur
        return Festival::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('start_date', '>=', now())
        ->orderBy('start_date');
    }

    public function changeView(string $view): void
    {
        $this->activeView = $view;
    }

    private function getUpcomingFestivals(): array
    {
        return $this->getTableQuery()->limit(5)->get()->toArray();
    }
}

