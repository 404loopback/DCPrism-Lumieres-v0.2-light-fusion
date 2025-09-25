<?php

namespace Modules\Fresnel\app\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Fresnel\app\Filament\Shared\Forms\Fields;
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
                        Fields::shieldRoleSelect()->live(),

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
                        
                        Toggle::make('is_partner')
                            ->label('Compte Provider/Partenaire')
                            ->helperText('Les comptes partenaires ne sont jamais désactivés automatiquement, même sans festival assigné')
                            ->default(false)
                            ->inline(false),

                        Select::make('festival_ids')
                            ->label('Festivals associés')
                            ->helperText(function (callable $get, $record) {
                                $roleNames = [];
                                
                                // Si c'est une édition et qu'on a un record, récupérer les rôles depuis le modèle
                                if ($record) {
                                    $roleNames = $record->roles->pluck('name')->toArray();
                                } else {
                                    // Si c'est une création, récupérer depuis les IDs sélectionnés
                                    $selectedRoleIds = $get('roles') ?? [];
                                    if (!empty($selectedRoleIds)) {
                                        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $selectedRoleIds)
                                            ->pluck('name')
                                            ->toArray();
                                    }
                                }
                                
                                // Vérifier les rôles assignés
                                if (in_array('admin', $roleNames) || in_array('super_admin', $roleNames)) {
                                    return 'Les administrateurs ont accès à tous les festivals par défaut, mais vous pouvez limiter l\'accès en sélectionnant des festivals spécifiques.';
                                } elseif (in_array('supervisor', $roleNames)) {
                                    return 'Les superviseurs ont un accès en lecture seule aux données du festival assigné. Sélectionnez le festival à superviser.';
                                } elseif (in_array('manager', $roleNames)) {
                                    return 'Les managers peuvent gérer les films et DCPs des festivals assignés.';
                                } elseif (in_array('tech', $roleNames)) {
                                    return 'Les techniciens valident les DCPs des festivals assignés.';
                                } elseif (in_array('source', $roleNames)) {
                                    return 'Les sources uploadent les contenus pour les festivals assignés.';
                                } elseif (in_array('cinema', $roleNames)) {
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
                            ->columnSpanFull()
                            ->live(), // Pour rendre réactif les films assignés
                    ])->columns(1),

Section::make('Films assignés')
                    ->description('Films que cette source peut uploader (uniquement pour les utilisateurs Source)')
                    ->visible(function (callable $get, $record) {
                        // Si c'est une édition avec un record, vérifier directement le rôle
                        if ($record) {
                            return $record->hasRole('source');
                        }
                        
                        // Si c'est une création, vérifier les rôles sélectionnés dans le formulaire
                        $selectedRoleIds = $get('roles') ?? [];
                        if (empty($selectedRoleIds)) {
                            return false;
                        }
                        
                        // Récupérer les noms des rôles depuis les IDs
                        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $selectedRoleIds)
                            ->pluck('name')
                            ->toArray();
                        
                        return in_array('source', $roleNames);
                    })
                    ->live()
                    ->schema([
                        Select::make('assigned_movie_ids')
                            ->label('Films assignés')
                            ->helperText('Films que cette source est autorisée à uploader')
                            ->options(function (callable $get, $record) {
                                // Récupérer les festivals associés à cet utilisateur
                                $userFestivalIds = [];
                                
                                if ($record) {
                                    // Utilisateur existant
                                    $userFestivalIds = $record->festivals->pluck('id')->toArray();
                                } else {
                                    // Création : récupérer depuis le formulaire
                                    $selectedFestivalIds = $get('festival_ids') ?? [];
                                    $userFestivalIds = $selectedFestivalIds;
                                }
                                
                                if (empty($userFestivalIds)) {
                                    return ['0' => 'Sélectionnez d\'abord des festivals pour cet utilisateur'];
                                }
                                
                                // Récupérer seulement les films des festivals associés à l'utilisateur
                                return \Modules\Fresnel\app\Models\Movie::with('festivals')
                                    ->whereHas('festivals', function ($query) use ($userFestivalIds) {
                                        $query->whereIn('festivals.id', $userFestivalIds);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($movie) {
                                        $festivals = $movie->festivals->pluck('name')->join(', ');
                                        $label = $movie->title . ($festivals ? ' (' . $festivals . ')' : '');
                                        return [$movie->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->default(function ($record) {
                                if (!$record || !$record->email) {
                                    return [];
                                }
                                return \Modules\Fresnel\app\Models\Movie::where('source_email', $record->email)
                                    ->pluck('id')
                                    ->toArray();
                            })
                            ->afterStateHydrated(function (Select $component, $state, $record) {
                                if ($record && $record->email) {
                                    $movieIds = \Modules\Fresnel\app\Models\Movie::where('source_email', $record->email)
                                        ->pluck('id')
                                        ->toArray();
                                    $component->state($movieIds);
                                }
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Rechercher et sélectionner des films...')
                            ->noSearchResultsMessage('Aucun film trouvé.')
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}
