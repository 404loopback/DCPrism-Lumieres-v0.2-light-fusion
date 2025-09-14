<?php

namespace Modules\Fresnel\app\Filament\Resources\Users\Schemas;

use Modules\Fresnel\app\Filament\Shared\Forms\Fields;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Modules\Fresnel\app\Models\Festival;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations utilisateur')
                    ->description('Créer un nouvel utilisateur')
                    ->schema([
                        // Utilisation des champs partagés
                        Fields::name('Nom complet', placeholder: 'Jean Dupont'),
                        Fields::email('Adresse email', unique: true, placeholder: 'jean.dupont@example.com'),
                        Fields::roleSelect(),
                        
                        // Champs de mot de passe partagés
                        ...Fields::password(),
                    ])->columns(2),
                
                Section::make('Configuration avancée')
                    ->description('Paramètres supplémentaires et accès')
                    ->schema([
                        Fields::isActive(
                            'Compte actif',
                            'Désactiver pour suspendre l\'accès de l\'utilisateur'
                        ),
                        
                        Select::make('festival_ids')
                            ->label('Festivals associés')
                            ->helperText(function (callable $get) {
                                $role = $get('role');
                                if ($role === 'admin') {
                                    return 'Les administrateurs ont accès à tous les festivals par défaut, mais vous pouvez limiter l\'accès en sélectionnant des festivals spécifiques.';
                                } elseif ($role === 'supervisor') {
                                    return 'Les superviseurs ont un accès en lecture seule aux données du festival assigné. Sélectionnez le festival à superviser.';
                                } elseif ($role === 'manager') {
                                    return 'Les managers peuvent gérer les films et DCPs des festivals assignés.';
                                } elseif ($role === 'tech') {
                                    return 'Les techniciens valident les DCPs des festivals assignés.';
                                } elseif ($role === 'source') {
                                    return 'Les sources uploadent les contenus pour les festivals assignés.';
                                } elseif ($role === 'cinema') {
                                    return 'Les cinémas accèdent aux DCPs des festivals assignés.';
                                }
                                return 'Sélectionnez les festivals auxquels cet utilisateur aura accès.';
                            })
                            ->options(function () {
                                return Festival::orderBy('name')->pluck('name', 'id')->toArray();
                            })
                            ->default(function ($record) {
                                return $record ? $record->festivals->pluck('id')->toArray() : [];
                            })
                            ->dehydrated()
                            ->afterStateHydrated(function (Select $component, $state, $record) {
                                if ($record) {
                                    $component->state($record->festivals->pluck('id')->toArray());
                                }
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Rechercher et sélectionner des festivals...')
                            ->noSearchResultsMessage('Aucun festival trouvé.')
                            ->maxItems(null)
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}
