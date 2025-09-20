<?php

namespace Modules\Fresnel\app\Filament\Resources\ValidationResults;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages\CreateValidationResult;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages\EditValidationResult;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\Pages\ListValidationResults;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\Schemas\ValidationResultForm;
use Modules\Fresnel\app\Filament\Resources\ValidationResults\Tables\ValidationResultTable;
use Modules\Fresnel\app\Models\ValidationResult;
use UnitEnum;

class ValidationResultResource extends Resource
{
    protected static ?string $model = ValidationResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?string $recordTitleAttribute = 'status';

    protected static ?string $navigationLabel = 'Validation Results';

    protected static ?string $modelLabel = 'Validation Result';

    protected static ?string $pluralModelLabel = 'Validation Results';

    protected static ?int $navigationSort = 22;

    // Masquer de la navigation - accessible uniquement via FilmsPage
    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion DCP';

    public static function form(Schema $schema): Schema
    {
        return ValidationResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValidationResultTable::configure($table);
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
