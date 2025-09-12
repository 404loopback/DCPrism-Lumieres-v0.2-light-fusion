<?php

namespace App\Filament\Resources\Festivals;

use App\Filament\Resources\Festivals\Pages\CreateFestival;
use App\Filament\Resources\Festivals\Pages\EditFestival;
use App\Filament\Resources\Festivals\Pages\ListFestivals;
use App\Filament\Resources\Festivals\Schemas\FestivalForm;
use App\Filament\Resources\Festivals\Tables\FestivalsTable;
use App\Models\Festival;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FestivalResource extends Resource
{
    protected static ?string $model = Festival::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $navigationLabel = 'Festivals';
    
    protected static ?string $modelLabel = 'Festival';
    
    protected static ?string $pluralModelLabel = 'Festivals';
    
    protected static ?int $navigationSort = 2;
    
    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return FestivalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FestivalsTable::configure($table);
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
            'index' => ListFestivals::route('/'),
            'create' => CreateFestival::route('/create'),
            'edit' => EditFestival::route('/{record}/edit'),
        ];
    }
}
