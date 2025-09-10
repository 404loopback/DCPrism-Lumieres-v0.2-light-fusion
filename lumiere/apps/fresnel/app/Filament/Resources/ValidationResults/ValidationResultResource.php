<?php

namespace App\Filament\Resources\ValidationResults;

use App\Filament\Resources\ValidationResults\Pages\CreateValidationResult;
use App\Filament\Resources\ValidationResults\Pages\EditValidationResult;
use App\Filament\Resources\ValidationResults\Pages\ListValidationResults;
use App\Filament\Resources\ValidationResults\Schemas\ValidationResultForm;
use App\Filament\Resources\ValidationResults\Tables\ValidationResultsTable;
use App\Models\ValidationResult;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ValidationResultResource extends Resource
{
    protected static ?string $model = ValidationResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;
    
    protected static ?string $recordTitleAttribute = 'status';
    
    protected static ?string $navigationLabel = 'Validation Results';
    
    protected static ?string $modelLabel = 'Validation Result';
    
    protected static ?string $pluralModelLabel = 'Validation Results';
    
    protected static ?int $navigationSort = 21;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion DCP';

    public static function form(Schema $schema): Schema
    {
        return ValidationResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValidationResultsTable::configure($table);
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
            'index' => ListValidationResults::route('/'),
            'create' => CreateValidationResult::route('/create'),
            'edit' => EditValidationResult::route('/{record}/edit'),
        ];
    }
}
