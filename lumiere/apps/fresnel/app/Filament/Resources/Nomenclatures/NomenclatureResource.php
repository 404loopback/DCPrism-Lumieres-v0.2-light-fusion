<?php

namespace App\Filament\Resources\Nomenclatures;

use App\Filament\Resources\Nomenclatures\Pages\CreateNomenclature;
use App\Filament\Resources\Nomenclatures\Pages\EditNomenclature;
use App\Filament\Resources\Nomenclatures\Pages\ListNomenclatures;
use App\Filament\Resources\Nomenclatures\Schemas\NomenclatureForm;
use App\Filament\Resources\Nomenclatures\Tables\NomenclaturesTable;
use App\Models\Nomenclature;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NomenclatureResource extends Resource
{
    protected static ?string $model = Nomenclature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    
    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $navigationLabel = 'Nomenclatures';
    
    protected static ?string $modelLabel = 'Nomenclature';
    
    protected static ?string $pluralModelLabel = 'Nomenclatures';
    
    protected static ?int $navigationSort = 13;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion de Contenu';

    public static function form(Schema $schema): Schema
    {
        return NomenclatureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NomenclaturesTable::configure($table);
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
