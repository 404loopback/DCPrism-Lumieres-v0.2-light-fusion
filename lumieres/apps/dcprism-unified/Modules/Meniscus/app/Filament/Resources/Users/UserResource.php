<?php

namespace Modules\Meniscus\app\Filament\Resources\Users;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Fresnel\app\Models\User;
use Modules\Meniscus\app\Filament\Resources\Users\Pages\ManageUsers;
use Modules\Fresnel\app\Filament\Shared\Forms\Fields;
use Modules\Fresnel\app\Filament\Shared\Tables\Columns;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fields::name('Nom complet'),
                Fields::email('Adresse email', unique: true),
                Fields::shieldRoleSelect('Rôles', multiple: true),
                
                // Champs de mot de passe (uniquement pour création)
                ...Fields::password(),
                
                Fields::isActive('Compte actif', 'Désactiver pour suspendre l\'accès de l\'utilisateur'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns::name('name', 'Nom'),
                Columns::emailWithVerification(),
                Columns::roleBadge(),
                Columns::activeToggle(),
                
                Columns::dateTime(
                    'last_login_at',
                    'Dernière connexion',
                    'd/m/Y H:i',
                    since: true,
                    toggleable: true
                )->placeholder('Jamais connecté'),
                
                Columns::createdAt(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                    
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
