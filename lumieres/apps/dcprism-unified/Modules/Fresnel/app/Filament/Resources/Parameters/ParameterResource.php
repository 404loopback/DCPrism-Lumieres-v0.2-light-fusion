<?php

namespace Modules\Fresnel\app\Filament\Resources\Parameters;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Parameters\Pages\CreateParameter;
use Modules\Fresnel\app\Filament\Resources\Parameters\Pages\EditParameter;
use Modules\Fresnel\app\Filament\Resources\Parameters\Pages\ListParameters;
use Modules\Fresnel\app\Filament\Resources\Parameters\Schemas\ParameterForm;
use Modules\Fresnel\app\Filament\Resources\Parameters\Tables\ParameterTable;
use Modules\Fresnel\app\Models\Parameter;
use UnitEnum;

class ParameterResource extends Resource
{
    protected static ?string $model = Parameter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Paramètres Globaux';

    protected static ?string $modelLabel = 'Paramètre Global';

    protected static ?string $pluralModelLabel = 'Paramètres Globaux';

    protected static ?int $navigationSort = 1;

    // Masquer de la navigation - accessible uniquement via AdministrationPage
    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion de Contenu';

    public static function form(Schema $schema): Schema
    {
        return ParameterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParameterTable::configure($table);
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
            'index' => ListParameters::route('/'),
            'create' => CreateParameter::route('/create'),
            'edit' => EditParameter::route('/{record}/edit'),
        ];
    }
}
