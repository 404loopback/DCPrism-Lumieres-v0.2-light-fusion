<?php

namespace App\Filament\Resources\MovieMetadata;

use App\Filament\Resources\MovieMetadata\Pages\CreateMovieMetadata;
use App\Filament\Resources\MovieMetadata\Pages\EditMovieMetadata;
use App\Filament\Resources\MovieMetadata\Pages\ListMovieMetadata;
use App\Filament\Resources\MovieMetadata\Schemas\MovieMetadataForm;
use App\Filament\Resources\MovieMetadata\Tables\MovieMetadataTable;
use App\Models\MovieMetadata;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MovieMetadataResource extends Resource
{
    protected static ?string $model = MovieMetadata::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'title';
    
    protected static ?string $navigationLabel = 'Movie Metadatas';
    
    protected static ?string $modelLabel = 'Movie Metadata';
    
    protected static ?string $pluralModelLabel = 'Movie Metadatas';
    
    protected static ?int $navigationSort = 12;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion de Contenu';

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
