<?php

namespace Modules\Fresnel\app\Filament\Resources\MovieMetadata;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages\CreateMovieMetadata;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages\EditMovieMetadata;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\Pages\ListMovieMetadata;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\Schemas\MovieMetadataForm;
use Modules\Fresnel\app\Filament\Resources\MovieMetadata\Tables\MovieMetadataTable;
use Modules\Fresnel\app\Models\MovieMetadata;
use UnitEnum;

class MovieMetadataResource extends Resource
{
    protected static ?string $model = MovieMetadata::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Métadonnées Films';

    protected static ?string $modelLabel = 'Métadonnée Film';

    protected static ?string $pluralModelLabel = 'Métadonnées Films';

    protected static ?int $navigationSort = 5;

    // Réactivé dans la navigation - accessible directement + via FilmsPage
    protected static bool $shouldRegisterNavigation = true;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion du Contenu';

    public static function form(Schema $schema): Schema
    {
        return MovieMetadataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovieMetadataTable::configure($table);
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
            'index' => ListMovieMetadata::route('/'),
            'create' => CreateMovieMetadata::route('/create'),
            'edit' => EditMovieMetadata::route('/{record}/edit'),
        ];
    }
}
