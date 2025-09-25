<?php

namespace Modules\Fresnel\app\Filament\Source\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\Movie;
use UnitEnum;

class MovieResourcesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Ressources Films';

    protected static ?string $title = 'Ressources de mes Films';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion Contenu';

    protected string $view = 'filament.pages.source.movie-resources';

    public string $activeTab = 'assets';
    public ?int $selectedMovieId = null;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasRole('source');
    }

    public function mount(): void
    {
        // Initialisation avec le premier film de la source
        $firstMovie = Movie::where('source_email', auth()->user()?->email)->first();
        $this->selectedMovieId = $firstMovie?->id;
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'selectedMovie' => $this->getSelectedMovie(),
            'userMovies' => $this->getUserMovies(),
            'resourceCategories' => $this->getResourceCategories(),
            'activeTab' => $this->activeTab,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // À implémenter : colonnes selon le type de ressource
            ])
            ->filters([
                // À implémenter : filtres par type, statut, date
            ])
            ->actions([
                // À implémenter : actions upload, download, edit
            ]);
    }

    protected function getTableQuery()
    {
        if (!$this->selectedMovieId) {
            return Movie::query()->whereRaw('1 = 0');
        }

        // À implémenter : requête selon le type de ressource
        return match ($this->activeTab) {
            'assets' => $this->getAssetsQuery(),
            'documents' => $this->getDocumentsQuery(), 
            'metadata' => $this->getMetadataQuery(),
            'versions' => $this->getVersionsQuery(),
            default => $this->getAssetsQuery(),
        };
    }

    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function selectMovie(int $movieId): void
    {
        $this->selectedMovieId = $movieId;
        $this->resetTable();
    }

    private function getSelectedMovie()
    {
        if (!$this->selectedMovieId) {
            return null;
        }

        return Movie::where('source_email', auth()->user()?->email)
            ->find($this->selectedMovieId);
    }

    private function getUserMovies()
    {
        return Movie::where('source_email', auth()->user()?->email)
            ->orderBy('title')
            ->get();
    }

    private function getResourceCategories(): array
    {
        return [
            'assets' => 'Assets visuels (affiches, stills)',
            'documents' => 'Documents (contrats, specs)',
            'metadata' => 'Métadonnées et informations',
            'versions' => 'Versions et DCPs',
        ];
    }

    private function getAssetsQuery()
    {
        // À implémenter : assets visuels du film
        return Movie::query()->whereRaw('1 = 0');
    }

    private function getDocumentsQuery()
    {
        // À implémenter : documents liés au film
        return Movie::query()->whereRaw('1 = 0');
    }

    private function getMetadataQuery()
    {
        // À implémenter : métadonnées du film
        return Movie::query()->whereRaw('1 = 0');
    }

    private function getVersionsQuery()
    {
        // À implémenter : versions et DCPs du film
        if (!$this->selectedMovieId) {
            return Movie::query()->whereRaw('1 = 0');
        }

        // Pour l'instant retourner une query vide, à remplacer par movie->versions() plus tard
        return Movie::query()->whereRaw('1 = 0');
    }
}
