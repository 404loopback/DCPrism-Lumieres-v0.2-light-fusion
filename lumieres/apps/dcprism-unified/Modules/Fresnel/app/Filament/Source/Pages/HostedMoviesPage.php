<?php

namespace Modules\Fresnel\app\Filament\Source\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\Movie;
use UnitEnum;

class HostedMoviesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationLabel = 'Mes Films Hébergés';

    protected static ?string $title = 'Films Hébergés';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion Contenu';

    protected string $view = 'filament.pages.source.hosted-movies';

    public string $activeTab = 'active';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('source');
    }

    public function mount(): void
    {
        // Initialisation des données pour cette source
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'totalMovies' => $this->getTotalHostedMovies(),
            'activeMovies' => $this->getActiveHostedMovies(),
            'archivedMovies' => $this->getArchivedHostedMovies(),
            'activeTab' => $this->activeTab,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes pour les films hébergés
            ])
            ->filters([
                // À implémenter : filtres par statut, festival, etc.
            ])
            ->actions([
                // À implémenter : actions spécifiques aux films hébergés
            ]);
    }

    protected function getTableQuery()
    {
        $userEmail = auth()->user()?->email;
        
        if (!$userEmail) {
            return Movie::query()->whereRaw('1 = 0');
        }
        
        $query = Movie::where('source_email', $userEmail);

        return match ($this->activeTab) {
            'active' => $query->where('is_active', true),
            'archived' => $query->where('is_active', false),
            default => $query,
        };
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    private function getTotalHostedMovies(): int
    {
        return Movie::where('source_email', auth()->user()?->email)->count();
    }

    private function getActiveHostedMovies(): int
    {
        return Movie::where('source_email', auth()->user()?->email)
            ->where('is_active', true)->count();
    }

    private function getArchivedHostedMovies(): int
    {
        return Movie::where('source_email', auth()->user()?->email)
            ->where('is_active', false)->count();
    }
}
