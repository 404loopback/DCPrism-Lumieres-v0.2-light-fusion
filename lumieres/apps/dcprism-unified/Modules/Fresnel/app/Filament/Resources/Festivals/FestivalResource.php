<?php

namespace Modules\Fresnel\app\Filament\Resources\Festivals;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Festivals\Pages\CreateFestival;
use Modules\Fresnel\app\Filament\Resources\Festivals\Pages\EditFestival;
use Modules\Fresnel\app\Filament\Resources\Festivals\Pages\ListFestivals;
use Modules\Fresnel\app\Filament\Resources\Festivals\Schemas\FestivalForm;
use Modules\Fresnel\app\Filament\Resources\Festivals\Tables\FestivalTable;
use Modules\Fresnel\app\Models\Festival;
use UnitEnum;

class FestivalResource extends Resource
{
    protected static ?string $model = Festival::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Festivals';

    protected static ?string $modelLabel = 'Festival';

    protected static ?string $pluralModelLabel = 'Festivals';

    protected static ?int $navigationSort = 2;

    // Masquer de la navigation - accessible uniquement via AdministrationPage
    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return FestivalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FestivalTable::configure($table);
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
