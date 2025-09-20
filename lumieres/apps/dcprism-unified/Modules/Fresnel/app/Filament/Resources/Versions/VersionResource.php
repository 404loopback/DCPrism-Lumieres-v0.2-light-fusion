<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Resources\Versions\Pages\CreateVersion;
use Modules\Fresnel\app\Filament\Resources\Versions\Pages\EditVersion;
use Modules\Fresnel\app\Filament\Resources\Versions\Pages\ListVersions;
use Modules\Fresnel\app\Filament\Resources\Versions\Pages\ViewVersion;
use Modules\Fresnel\app\Filament\Resources\Versions\Schemas\VersionForm;
use Modules\Fresnel\app\Filament\Resources\Versions\Schemas\VersionInfolist;
use Modules\Fresnel\app\Filament\Resources\Versions\Tables\VersionTable;
use Modules\Fresnel\app\Models\Version;
use UnitEnum;

class VersionResource extends Resource
{
    protected static ?string $model = Version::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $navigationLabel = 'Versions';

    protected static ?string $modelLabel = 'Version';

    protected static ?string $pluralModelLabel = 'Versions';

    protected static ?int $navigationSort = 21;

    // Masquer de la navigation - accessible uniquement via FilmsPage
    protected static bool $shouldRegisterNavigation = false;

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
        return VersionTable::configure($table);
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
