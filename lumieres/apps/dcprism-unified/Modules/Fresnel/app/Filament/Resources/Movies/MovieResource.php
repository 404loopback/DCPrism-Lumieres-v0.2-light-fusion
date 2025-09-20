<?php

namespace Modules\Fresnel\app\Filament\Resources\Movies;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Movies\Pages\CreateMovie;
use Modules\Fresnel\app\Filament\Resources\Movies\Pages\EditMovie;
use Modules\Fresnel\app\Filament\Resources\Movies\Pages\ListMovies;
use Modules\Fresnel\app\Filament\Resources\Movies\Pages\ViewMovie;
use Modules\Fresnel\app\Filament\Resources\Movies\Schemas\MovieForm;
use Modules\Fresnel\app\Filament\Resources\Movies\Schemas\MovieInfolist;
use Modules\Fresnel\app\Filament\Resources\Movies\Tables\MovieTable;
use Modules\Fresnel\app\Models\Movie;
use UnitEnum;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Films';

    protected static ?string $modelLabel = 'Film';

    protected static ?string $pluralModelLabel = 'Films';

    protected static ?int $navigationSort = 11;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion de Contenu';

    // Masquer de la navigation - accessible uniquement via FilmsPage
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return MovieForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MovieInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovieTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovies::route('/'),
            'create' => CreateMovie::route('/create'),
            'view' => ViewMovie::route('/{record}'),
            'edit' => EditMovie::route('/{record}/edit'),
        ];
    }
}
