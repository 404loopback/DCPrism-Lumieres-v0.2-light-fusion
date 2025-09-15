<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps;

use Modules\Fresnel\app\Filament\Resources\Dcps\Pages\CreateDcp;
use Modules\Fresnel\app\Filament\Resources\Dcps\Pages\EditDcp;
use Modules\Fresnel\app\Filament\Resources\Dcps\Pages\ListDcps;
use Modules\Fresnel\app\Filament\Resources\Dcps\Pages\ViewDcp;
use Modules\Fresnel\app\Filament\Resources\Dcps\Schemas\DcpForm;
use Modules\Fresnel\app\Filament\Resources\Dcps\Schemas\DcpInfolist;
use Modules\Fresnel\app\Filament\Resources\Dcps\Tables\DcpTable;
use Modules\Fresnel\app\Models\Dcp;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DcpResource extends Resource
{
    protected static ?string $model = Dcp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $navigationLabel = 'DCPs';
    
    protected static ?string $modelLabel = 'DCP';
    
    protected static ?string $pluralModelLabel = 'DCPs';
    
    protected static ?int $navigationSort = 20;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion DCP';
    
    // Masquer de la navigation - accessible uniquement via FilmsPage
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return DcpForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DcpInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DcpTable::configure($table);
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
            'index' => ListDcps::route('/'),
            'create' => CreateDcp::route('/create'),
            'view' => ViewDcp::route('/{record}'),
            'edit' => EditDcp::route('/{record}/edit'),
        ];
    }
}
