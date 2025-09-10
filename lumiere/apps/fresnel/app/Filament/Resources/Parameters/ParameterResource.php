<?php

namespace App\Filament\Resources\Parameters;

use App\Filament\Resources\Parameters\Pages\CreateParameter;
use App\Filament\Resources\Parameters\Pages\EditParameter;
use App\Filament\Resources\Parameters\Pages\ListParameters;
use App\Filament\Resources\Parameters\Schemas\ParameterForm;
use App\Filament\Resources\Parameters\Tables\ParametersTable;
use App\Models\Parameter;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParameterResource extends Resource
{
    protected static ?string $model = Parameter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $navigationLabel = 'Paramètres Globaux';
    
    protected static ?string $modelLabel = 'Paramètre Global';
    
    protected static ?string $pluralModelLabel = 'Paramètres Globaux';
    
    protected static ?int $navigationSort = 10;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion de Contenu';

    public static function form(Schema $schema): Schema
    {
        return ParameterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParametersTable::configure($table);
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
