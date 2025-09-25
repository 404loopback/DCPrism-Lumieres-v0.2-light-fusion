<?php

namespace Modules\Fresnel\app\Filament\Festival\Resources\Movies;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\Pages\CreateMovie;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\Pages\EditMovie;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\Pages\ListMovies;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\Schemas\MovieForm;
use Modules\Fresnel\app\Filament\Festival\Resources\Movies\Tables\MovieTable;
use Modules\Fresnel\app\Models\Movie;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Films';

    protected static ?string $pluralModelLabel = 'Films';

    protected static ?string $modelLabel = 'Film';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MovieForm::configure($schema);
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
            'edit' => EditMovie::route('/{record}/edit'),
        ];
    }
}
