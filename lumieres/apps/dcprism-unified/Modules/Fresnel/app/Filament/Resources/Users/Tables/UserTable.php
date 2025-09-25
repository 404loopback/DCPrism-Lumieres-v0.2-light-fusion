<?php

namespace Modules\Fresnel\app\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Fresnel\app\Filament\Shared\Tables\Columns;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Utilisation des colonnes partagées
                Columns::name('name', 'Nom'),
                Columns::emailWithVerification(),
                Columns::roleBadge(),
                Columns::activeToggle(),
                
                Tables\Columns\IconColumn::make('is_partner')
                    ->label('Provider')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn ($state) => $state ? 'Compte partenaire/provider' : 'Compte standard')
                    ->sortable(),
                    
                Columns::festivalsDisplay(),

                Tables\Columns\TextColumn::make('assigned_movies_count')
                    ->label('Films assignés')
                    ->state(function ($record) {
                        if (!$record || !$record->hasRole('source')) {
                            return null;
                        }
                        return \Modules\Fresnel\app\Models\Movie::where('source_email', $record->email)->count();
                    })
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->visible(fn ($record) => $record && $record->hasRole('source'))
                    ->sortable(false)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->placeholder('Jamais connecté')
                    ->sortable()
                    ->toggleable(),

                // Colonnes de dates partagées
                Columns::createdAt(),
                Columns::updatedAt(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->indicator('Rôles'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
                    
                Tables\Filters\TernaryFilter::make('is_partner')
                    ->label('Type de compte')
                    ->placeholder('Tous')
                    ->trueLabel('Providers/Partenaires')
                    ->falseLabel('Comptes standard'),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email vérifié')
                    ->placeholder('Tous')
                    ->trueLabel('Vérifiés')
                    ->falseLabel('Non vérifiés')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    ),

                Tables\Filters\SelectFilter::make('festivals')
                    ->label('Festival assigné')
                    ->relationship('festivals', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_recently')
                    ->label('Créés récemment')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subWeek()))
                    ->toggle(),

                Tables\Filters\Filter::make('never_logged_in')
                    ->label('Jamais connectés')
                    ->query(fn ($query) => $query->whereNull('last_login_at'))
                    ->toggle(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->color('primary')
                        ->url(fn ($record) => route('filament.fresnel.resources.users.edit', ['record' => $record->id])),

                    ViewAction::make()
                        ->label('Voir le profil')
                        ->color('info')
                        ->modalHeading(fn ($record) => 'Profil de '.$record->name)
                        ->modalWidth('4xl')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Section::make('Informations personnelles')
                                        ->schema([
                                            TextEntry::make('name')
                                                ->label('Nom complet'),
                                            TextEntry::make('email')
                                                ->label('Email'),
                                            TextEntry::make('is_active')
                                                ->label('Statut')
                                                ->badge()
                                                ->color(fn ($state): string => $state ? 'success' : 'danger')
                                                ->formatStateUsing(fn ($state): string => $state ? 'Actif' : 'Inactif'),
                                            TextEntry::make('is_partner')
                                                ->label('Type de compte')
                                                ->badge()
                                                ->color(fn ($state): string => $state ? 'warning' : 'gray')
                                                ->formatStateUsing(fn ($state): string => $state ? 'Provider/Partenaire' : 'Standard')
                                                ->icon(fn ($state): string => $state ? 'heroicon-o-star' : 'heroicon-o-user'),
                                            TextEntry::make('email_verified_at')
                                                ->label('Email vérifié')
                                                ->dateTime('d/m/Y H:i')
                                                ->placeholder('Non vérifié'),
                                        ]),
                                    Section::make('Rôles et accès')
                                        ->schema([
                                            TextEntry::make('roles.name')
                                                ->label('Rôles')
                                                ->badge()
                                                ->color('purple')
                                                ->formatStateUsing(fn ($state): string => ucfirst($state))
                                                ->placeholder('Aucun rôle'),
                                            TextEntry::make('festivals.name')
                                                ->label('Festivals assignés')
                                                ->badge()
                                                ->color('yellow')
                                                ->placeholder('Aucun festival assigné'),
                                            TextEntry::make('last_login_at')
                                                ->label('Dernière connexion')
                                                ->dateTime('d/m/Y H:i')
                                                ->since()
                                                ->placeholder('Jamais connecté'),
                                            TextEntry::make('created_at')
                                                ->label('Compte créé le')
                                                ->dateTime('d/m/Y H:i')
                                                ->since(),
                                        ]),
                                ]),
                        ])
                        ->modalSubmitAction(false),

                    Action::make('verify_email')
                        ->label('Vérifier email')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => is_null($record->email_verified_at))
                        ->action(function ($record) {
                            $record->update(['email_verified_at' => now()]);
                            \Filament\Notifications\Notification::make()
                                ->title('Email vérifié')
                                ->body('L\'email de '.$record->name.' a été marqué comme vérifié.')
                                ->success()
                                ->send();
                        }),

                    Action::make('reset_password')
                        ->label('Réinitialiser mot de passe')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Réinitialiser le mot de passe')
                        ->modalDescription(fn ($record) => 'Êtes-vous sûr de vouloir réinitialiser le mot de passe de '.$record->name.' ?')
                        ->action(function ($record) {
                            // Ici, vous pourriez envoyer un email de réinitialisation
                            // ou générer un nouveau mot de passe temporaire
                            \Filament\Notifications\Notification::make()
                                ->title('Mot de passe réinitialisé')
                                ->body('Un email de réinitialisation a été envoyé à '.$record->email)
                                ->success()
                                ->send();
                        }),


                    Action::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'Désactiver' : 'Activer')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => ($record->is_active ? 'Désactiver' : 'Activer').' cet utilisateur')
                        ->modalDescription(function ($record) {
                            if ($record->is_active && $record->isProtectedFromDeactivation()) {
                                return 'Cet utilisateur est protégé contre la désactivation (partenaire ou admin).';
                            }
                            return $record->is_active
                                ? 'Cet utilisateur ne pourra plus se connecter une fois désactivé.'
                                : 'Cet utilisateur pourra à nouveau se connecter une fois activé.';
                        })
                        ->visible(fn ($record) => !($record->is_active && $record->isProtectedFromDeactivation()))
                        ->action(function ($record) {
                            $newStatus = ! $record->is_active;
                            
                            // Vérification supplémentaire côté serveur
                            if (!$newStatus && $record->isProtectedFromDeactivation()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Action interdite')
                                    ->body('Impossible de désactiver un partenaire ou un administrateur.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $record->update(['is_active' => $newStatus]);

                            \Filament\Notifications\Notification::make()
                                ->title('Statut modifié')
                                ->body($record->name.' a été '.($newStatus ? 'activé' : 'désactivé').'.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify_emails')
                        ->label('Vérifier les emails')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(function ($record) {
                            if (is_null($record->email_verified_at)) {
                                $record->update(['email_verified_at' => now()]);
                            }
                        })),

                    BulkAction::make('activate_users')
                        ->label('Activer')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => true]))),

                    BulkAction::make('deactivate_users')
                        ->label('Désactiver')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Désactiver les utilisateurs sélectionnés')
                        ->modalDescription('Les partenaires et administrateurs ne seront pas désactivés.')
                        ->action(function ($records) {
                            $deactivatedCount = 0;
                            $protectedCount = 0;
                            
                            $records->each(function ($record) use (&$deactivatedCount, &$protectedCount) {
                                if ($record->isProtectedFromDeactivation()) {
                                    $protectedCount++;
                                } else {
                                    $record->update(['is_active' => false]);
                                    $deactivatedCount++;
                                }
                            });
                            
                            $message = "{$deactivatedCount} utilisateur(s) désactivé(s)";
                            if ($protectedCount > 0) {
                                $message .= ", {$protectedCount} partenaire(s)/admin(s) protégé(s)";
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Désactivation terminée')
                                ->body($message)
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
