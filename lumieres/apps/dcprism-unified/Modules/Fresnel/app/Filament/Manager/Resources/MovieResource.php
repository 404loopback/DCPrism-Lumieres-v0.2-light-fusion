<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use Modules\Fresnel\app\Models\Movie;
use App\Models\User;
use Modules\Fresnel\app\Services\Context\FestivalContextService;
use Modules\Fresnel\app\Services\MovieForm\MovieFormService;
use Modules\Fresnel\app\Services\VersionGenerationService;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Films';
    
    protected static ?string $modelLabel = 'Film';
    
    protected static ?string $pluralModelLabel = 'Films';
    
    protected static ?int $navigationSort = 2;
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestion Festival';

    /**
     * Get the festival context service
     */
    protected static function getFestivalContextService(): FestivalContextService
    {
        return app(FestivalContextService::class);
    }

    /**
     * Get the movie form service
     */
    protected static function getMovieFormService(): MovieFormService
    {
        return app(MovieFormService::class);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    // Étape 1 : Informations générales du film
                    Step::make('Informations du Film')
                        ->description('Titre et contact source')
                        ->icon('heroicon-o-film')
                        ->schema([
                            TextInput::make('title')
                                ->label('Titre du Film')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->helperText('Le titre principal du film qui sera utilisé dans les nomenclatures')
                                ->columnSpanFull(),
                                
                            TextInput::make('source_email')
                                ->label('Email de la Source')
                                ->email()
                                ->required()
                                ->helperText('Un compte utilisateur sera créé automatiquement si cet email n\'existe pas')
                                ->suffixIcon('heroicon-o-at-symbol')
                                ->columnSpanFull(),
                        ]),
                        
                    // Étape 2 : Création des versions avec paramètres
                    Step::make('Versions et Paramètres')
                        ->description('Créer les différentes versions DCP avec leurs paramètres techniques')
                        ->icon('heroicon-o-cube')
                        ->schema([
                            Repeater::make('versions')
                                ->label('Versions DCP du film')
                                ->relationship()
                                ->schema([
                                    // Nom généré automatiquement selon les paramètres
                                    TextInput::make('generated_nomenclature')
                                        ->label('Nomenclature générée')
                                        ->disabled()
                                        ->dehydrated()
                                        ->default('Sera générée automatiquement')
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            // Générer la nomenclature en temps réel
                                            $nomenclature = static::getMovieFormService()
                                                ->generateRealtimeNomenclature($get);
                                            if ($nomenclature) {
                                                $set('generated_nomenclature', $nomenclature);
                                            }
                                        })
                                        ->helperText('La nomenclature sera générée selon les paramètres choisis et les règles du festival')
                                        ->columnSpanFull(),
                                    
                                    // Paramètres organisés en onglets par catégorie
                                    ...static::getMovieFormService()->buildVersionParametersFields(),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['generated_nomenclature'] ?? 'Nouvelle version')
                                ->addActionLabel('Ajouter une version DCP')
                                ->collapsible()
                                ->cloneable()
                                ->deletable()
                                ->defaultItems(1)
                                ->columnSpanFull()
                                ->visible(fn ($operation) => $operation === 'create'),
                                
                            // Tableau des versions existantes pour l'édition
                            \Filament\Forms\Components\ViewField::make('versions_table')
->view('fresnel::filament.forms.components.versions-table')
                                ->viewData(fn ($record) => [
                                    'versions' => $record ? $record->versions()->with(['movie'])->get() : collect(),
                                    'operation' => 'edit'
                                ])
                                ->columnSpanFull()
                                ->visible(fn ($operation) => $operation === 'edit'),
                        ]),
                        
                    // Étape 3 : Cinémas liés (en développement)
                    Step::make('Cinémas')
                        ->description('Sélection des cinémas pour la distribution')
                        ->icon('heroicon-o-building-office-2')
                        ->schema([
                            Placeholder::make('cinemas_placeholder')
                                ->content('Cette fonctionnalité est en cours de développement. La gestion des cinémas liés sera disponible prochainement.')
                                ->columnSpanFull(),
                        ]),
                        
                    // Étape 4 : Séances liées (en développement)
                    Step::make('Séances')
                        ->description('Programmation des séances')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Placeholder::make('screenings_placeholder')
                                ->content('Cette fonctionnalité est en cours de développement. La gestion des séances sera disponible prochainement.')
                                ->columnSpanFull(),
                        ]),
                ])
                ->submitAction('Créer le film')
                ->skippable(fn () => true) // Toutes les étapes sont facultatives par défaut
                ->persistStepInQueryString()
                ->columnSpanFull(),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('source_email')
                    ->label('Source')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-at-symbol'),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'gray' => Movie::STATUS_FILM_CREATED,
                        'warning' => Movie::STATUS_SOURCE_VALIDATED,
                        'info' => Movie::STATUS_VERSIONS_VALIDATED,
                        'success' => Movie::STATUS_UPLOADS_OK,
                        'danger' => Movie::STATUS_UPLOAD_ERROR,
                        'primary' => Movie::STATUS_VALIDATION_OK,
                        'success' => Movie::STATUS_DISTRIBUTION_OK,
                        'danger' => Movie::STATUS_VALIDATION_ERROR,
                    ])
                    ->formatStateUsing(fn ($state) => Movie::getStatuses()[$state] ?? $state),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Movie::getStatuses()),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('notify_source')
                        ->label('Notifier la Source')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Notifier la source')
                        ->modalDescription(fn (Movie $record): string => "Envoyer un email de notification à {$record->source_email} ?")
                        ->action(function (Movie $record) {
                            // Envoi d'email à la source via MailingService
                            $mailingService = app(\App\Services\MailingService::class);
                            
                            $message = match($record->status) {
                                Movie::STATUS_FILM_CREATED => "Votre film '{$record->title}' a été créé dans le système DCPrism. Vous pouvez maintenant accéder à votre espace pour suivre son évolution.",
                                Movie::STATUS_SOURCE_VALIDATED => "Votre film '{$record->title}' a été validé. L'équipe technique va maintenant procéder à la validation des versions.",
                                default => "Mise à jour concernant votre film '{$record->title}' dans DCPrism."
                            };
                            
                            $success = $mailingService->sendSourceNotification(
                                $record, 
                                $message,
                                "Mise à jour pour votre film: {$record->title}"
                            );
                            
                            if ($success) {
                                Notification::make()
                                    ->title('Email envoyé avec succès')
                                    ->body("La source {$record->source_email} a été notifiée par email.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Erreur d\'envoi email')
                                    ->body("Impossible d'envoyer l'email à {$record->source_email}. Voir les logs pour plus de détails.")
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Movie $record) => in_array($record->status, [Movie::STATUS_FILM_CREATED, Movie::STATUS_SOURCE_VALIDATED])),
                        
                    EditAction::make()
                        ->label('Éditer'),
                        
                    DeleteAction::make()
                        ->label('Supprimer')
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer le film')
                        ->modalDescription(fn (Movie $record): string => "Êtes-vous sûr de vouloir supprimer définitivement le film '{$record->title}' ?"),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Filtrer les films pour le festival sélectionné
     */
    public static function getEloquentQuery(): Builder
    {
        $festivalId = static::getFestivalContextService()->getCurrentFestivalId();
        
        if (!$festivalId) {
            // Si aucun festival sélectionné, retourner une query vide
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        return parent::getEloquentQuery()
            ->whereHas('festivals', function (Builder $query) use ($festivalId) {
                $query->where('festival_id', $festivalId);
            });
    }

    /**
     * Hook avant création : associer au festival et créer le compte Source
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Vérifier qu'un festival est sélectionné
        $festival = static::getFestivalContextService()->requireFestivalSelected();
        
        // Créer ou récupérer l'utilisateur Source
        $sourceUser = static::getOrCreateSourceUser($data['source_email']);
        
        // Le film sera associé au festival après création via l'event created
        $data['created_by'] = auth()->id();
        $data['status'] = Movie::STATUS_FILM_CREATED;
        
        return $data;
    }

    /**
     * Hook après création : créer les associations, paramètres et versions manuelles
     */
    public static function afterCreate($record, array $data): void
    {
        $movieFormService = static::getMovieFormService();
        
        DB::transaction(function () use ($record, $data, $movieFormService) {
            // Vérifier qu'un festival est sélectionné
            $festival = static::getFestivalContextService()->requireFestivalSelected();
            
            // 1. Associer le film aux festivals sélectionnés
            $movieFormService->associateMovieToFestivals($record, $data);
            
            // 2. Créer les paramètres du festival pour le film
            $movieFormService->createMovieParametersFromFormData($record, $data);
            
            // 3. Traitement des versions créées via le Repeater
            $versionCount = 0;
            if (!empty($data['versions'])) {
                foreach ($data['versions'] as $versionData) {
                    // Générer la nomenclature complète
                    $nomenclature = $movieFormService->generateVersionNomenclature(
                        $record, 
                        $versionData
                    );
                    
                    // Mettre à jour la version existante avec la nomenclature
                    $version = \Modules\Fresnel\app\Models\Version::where('movie_id', $record->id)
                        ->where('type', $versionData['type'] ?? 'Nouvelle version')
                        ->first();
                        
                    if ($version) {
                        $version->update([
                            'generated_nomenclature' => $nomenclature
                        ]);
                        $versionCount++;
                    }
                }
            }
            
            // Si aucune version manuelle créée, générer automatiquement
            if ($versionCount === 0) {
                try {
                    $versionService = new VersionGenerationService();
                    $generatedVersions = $versionService->generateVersionsForMovie($record, $festival->id);
                    $versionCount = count($generatedVersions);
                    
                    Notification::make()
                        ->title('Versions générées automatiquement')
                        ->body("{$versionCount} version(s) générée(s) automatiquement selon les nomenclatures du festival.")
                        ->info()
                        ->send();
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de la génération des versions', [
                        'movie_id' => $record->id,
                        'festival_id' => $festival->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Créer une version par défaut en cas d'erreur
                    \Modules\Fresnel\app\Models\Version::create([
                        'movie_id' => $record->id,
                        'type' => 'VO',
                        'audio_lang' => 'original',
                        'sub_lang' => null,
                        'accessibility' => null,
                        'ov_id' => null,
                        'vf_ids' => null,
                        'generated_nomenclature' => $record->title . '_VO_DEFAULT'
                    ]);
                    $versionCount = 1;
                    
                    Notification::make()
                        ->title('Version par défaut créée')
                        ->body('Erreur lors de la génération automatique. Une version par défaut (VO) a été créée.')
                        ->warning()
                        ->send();
                }
            } else {
                // Notification pour les versions manuelles
                Notification::make()
                    ->title('Versions créées manuellement')
                    ->body("{$versionCount} version(s) créée(s) manuellement pour le film '{$record->title}'.")
                    ->success()
                    ->send();
            }
        });
    }

    /**
     * Créer ou récupérer un utilisateur Source
     */
    protected static function getOrCreateSourceUser(string $email): User
    {
        // Vérifier si l'utilisateur existe déjà
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            // Si l'utilisateur existe mais n'a pas le rôle Source, l'assigner
            if (!$existingUser->hasRole('source')) {
                $existingUser->assignRole('source');
            }
            
            return $existingUser;
        }
        
        // Créer un nouvel utilisateur Source
        $password = Str::random(12);
        
        $user = User::create([
            'name' => explode('@', $email)[0], // Nom basé sur la partie avant @
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(), // Auto-vérifier pour les Sources créées par Manager
        ]);
        
        // Assigner le rôle Source via Shield
        $user->assignRole('source');
        
        // Envoyer email avec les identifiants via MailingService
        $mailingService = app(\App\Services\MailingService::class);
        $emailSent = $mailingService->sendSourceAccountCreated($user, $password);
        
        // Notification au Manager
        Notification::make()
            ->title('Compte Source créé')
            ->body("Nouveau compte Source créé pour {$email}. " . ($emailSent ? 'Email envoyé.' : 'Erreur envoi email.'))
            ->success()
            ->send();
        
        return $user;
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
            'index' => \Modules\Fresnel\app\Filament\Manager\Resources\MovieResource\Pages\ListMovies::route('/'),
            'create' => \Modules\Fresnel\app\Filament\Manager\Resources\MovieResource\Pages\CreateMovie::route('/create'),
            'edit' => \Modules\Fresnel\app\Filament\Manager\Resources\MovieResource\Pages\EditMovie::route('/{record}/edit'),
        ];
    }
}
