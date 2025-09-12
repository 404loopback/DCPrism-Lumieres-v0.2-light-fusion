<?php

namespace App\Filament\Resources\Versions;

use App\Filament\Resources\Versions\Pages\CreateVersion;
use App\Filament\Resources\Versions\Pages\EditVersion;
use App\Filament\Resources\Versions\Pages\ListVersions;
use App\Filament\Resources\Versions\Pages\ViewVersion;
use App\Filament\Resources\Versions\Schemas\VersionForm;
use App\Filament\Resources\Versions\Schemas\VersionInfolist;
use App\Filament\Resources\Versions\Tables\VersionsTable;
use App\Models\Version;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VersionResource extends Resource
{
    protected static ?string $model = Version::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'type';
    
    protected static ?string $navigationLabel = 'Versions';
    
    protected static ?string $modelLabel = 'Version';
    
    protected static ?string $pluralModelLabel = 'Versions';
    
    protected static ?int $navigationSort = 31;
    
    protected static string|UnitEnum|null $navigationGroup = 'Configuration DCP';

    public static function form(Schema $schema): Schema
    {
        return VersionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VersionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VersionsTable::configure($table);
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
            'index' => ListVersions::route('/'),
            'create' => CreateVersion::route('/create'),
            'view' => ViewVersion::route('/{record}'),
            'edit' => EditVersion::route('/{record}/edit'),
        ];
    }
}
