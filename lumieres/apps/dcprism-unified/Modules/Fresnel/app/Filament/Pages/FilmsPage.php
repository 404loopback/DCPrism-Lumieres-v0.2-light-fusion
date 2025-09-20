<?php

namespace Modules\Fresnel\app\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Dcps\Tables\DcpTable;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\Tables\MovieMetadataTable;
use Modules\Fresnel\app\Filament\Resources\Movies\Tables\MovieTable;
use Modules\Fresnel\app\Filament\Resources\Versions\Tables\VersionTable;
use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\MovieMetadata;
use Modules\Fresnel\app\Models\Version;

class FilmsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Films';

    protected static ?string $title = 'Gestion Films';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.films';

    public string $activeTab = 'films';

    /**
     * Table des Films
     */
    public function moviesTable(Table $table): Table
    {
        return MovieTable::configure(
            $table->query(Movie::query())
        );
    }

    /**
     * Table des Versions
     */
    public function versionsTable(Table $table): Table
    {
        return VersionTable::configure(
            $table->query(Version::query())
        );
    }

    /**
     * Table des DCPs
     */
    public function dcpsTable(Table $table): Table
    {
        return DcpTable::configure(
            $table->query(Dcp::query())
        );
    }

    /**
     * Table des Métadonnées
     */
    public function movieMetadataTable(Table $table): Table
    {
        return MovieMetadataTable::configure(
            $table->query(MovieMetadata::query())
        );
    }

    /**
     * Changer d'onglet
     */
    public function changeTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    /**
     * Récupérer la table active
     */
    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'films' => $this->moviesTable($table),
            'versions' => $this->versionsTable($table),
            'dcps' => $this->dcpsTable($table),
            'metadata' => $this->movieMetadataTable($table),
            default => $this->moviesTable($table),
        };
    }
}
