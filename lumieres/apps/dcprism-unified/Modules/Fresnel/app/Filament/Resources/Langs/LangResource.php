<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs;

use Modules\Fresnel\app\Filament\Resources\Langs\Pages\CreateLang;
use Modules\Fresnel\app\Filament\Resources\Langs\Pages\EditLang;
use Modules\Fresnel\app\Filament\Resources\Langs\Pages\ListLangs;
use Modules\Fresnel\app\Filament\Resources\Langs\Pages\ViewLang;
use Modules\Fresnel\app\Filament\Resources\Langs\Schemas\LangForm;
use Modules\Fresnel\app\Filament\Resources\Langs\Schemas\LangInfolist;
use Modules\Fresnel\app\Filament\Resources\Langs\Tables\LangsTable;
use Modules\Fresnel\app\Models\Lang;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LangResource extends Resource
{
    protected static ?string $model = Lang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $navigationLabel = 'Langues';
    
    protected static ?string $modelLabel = 'Langue';
    
    protected static ?string $pluralModelLabel = 'Langues';
    
    protected static ?int $navigationSort = 30;
    
    protected static string|UnitEnum|null $navigationGroup = 'Configuration DCP';

    public static function form(Schema $schema): Schema
    {
        return LangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LangsTable::configure($table);
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
            'index' => ListLangs::route('/'),
            'create' => CreateLang::route('/create'),
            'view' => ViewLang::route('/{record}'),
            'edit' => EditLang::route('/{record}/edit'),
        ];
    }
}
