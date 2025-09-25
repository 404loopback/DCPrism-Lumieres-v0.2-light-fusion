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

    protected static ?string $navigationLabel = 'Résultats de Validation';

    protected static ?string $modelLabel = 'Résultat de Validation';

    protected static ?string $pluralModelLabel = 'Résultats de Validation';

    protected static ?int $navigationSort = 4;

    // Réactivé dans la navigation - accessible directement + via FilmsPage
    protected static bool $shouldRegisterNavigation = true;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion du Contenu';

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
