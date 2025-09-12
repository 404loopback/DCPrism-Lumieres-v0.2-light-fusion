<?php

namespace App\Filament\Resources\Movies;

use App\Filament\Resources\Movies\Pages\CreateMovie;
use App\Filament\Resources\Movies\Pages\EditMovie;
use App\Filament\Resources\Movies\Pages\ListMovies;
use App\Filament\Resources\Movies\Pages\ViewMovie;
use App\Filament\Resources\Movies\Schemas\MovieForm;
use App\Filament\Resources\Movies\Schemas\MovieInfolist;
use App\Filament\Resources\Movies\Tables\MoviesTable;
use App\Models\Movie;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
        return MoviesTable::configure($table);
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
