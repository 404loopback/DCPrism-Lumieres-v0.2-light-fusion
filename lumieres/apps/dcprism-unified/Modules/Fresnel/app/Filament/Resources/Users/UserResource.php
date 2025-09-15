<?php

namespace Modules\Fresnel\app\Filament\Resources\Users;

use Modules\Fresnel\app\Filament\Resources\Users\Pages\CreateUser;
use Modules\Fresnel\app\Filament\Resources\Users\Pages\EditUser;
use Modules\Fresnel\app\Filament\Resources\Users\Pages\ListUsers;
use Modules\Fresnel\app\Filament\Resources\Users\Schemas\UserForm;
use Modules\Fresnel\app\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $navigationLabel = 'Utilisateurs';
    
    protected static ?string $modelLabel = 'Utilisateur';
    
    protected static ?string $pluralModelLabel = 'Utilisateurs';
    
    protected static ?int $navigationSort = 3;
    
    // Masquer de la navigation - accessible uniquement via AdministrationPage
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
    
}
