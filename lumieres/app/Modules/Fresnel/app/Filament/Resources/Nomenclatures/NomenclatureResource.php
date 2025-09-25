<?php

namespace Modules\Fresnel\app\Filament\Resources\Nomenclatures;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Pages\CreateNomenclature;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Pages\EditNomenclature;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Pages\ListNomenclatures;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Schemas\NomenclatureForm;
use Modules\Fresnel\app\Filament\Resources\Nomenclatures\Tables\NomenclatureTable;
use Modules\Fresnel\app\Models\Nomenclature;
use UnitEnum;

class NomenclatureResource extends Resource
{
    protected static ?string $model = Nomenclature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Nomenclatures';

    protected static ?string $modelLabel = 'Nomenclature';

    protected static ?string $pluralModelLabel = 'Nomenclatures';

    protected static ?int $navigationSort = 3;

    // Réactivé dans la navigation - accessible directement + via AdministrationPage
    protected static bool $shouldRegisterNavigation = true;

    protected static string|UnitEnum|null $navigationGroup = 'Festivals';

    public static function form(Schema $schema): Schema
    {
        return NomenclatureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NomenclatureTable::configure($table);
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
            'index' => ListNomenclatures::route('/'),
            'create' => CreateNomenclature::route('/create'),
            'edit' => EditNomenclature::route('/{record}/edit'),
        ];
    }
}
