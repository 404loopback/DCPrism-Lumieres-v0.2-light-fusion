<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use Modules\Fresnel\app\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Services\Context\FestivalContextService;
use Modules\Fresnel\app\Services\MovieForm\MovieFormService;
use Modules\Fresnel\app\Services\VersionGenerationService;
use Modules\Fresnel\app\Services\FestivalAccessService;
use Illuminate\Support\Facades\Session;
use UnitEnum;

/**
 * Ressource Filament pour la gestion des films avec restrictions d'accès par festival.
 * 
 * Cette ressource utilise le système de scoping basé sur les festivals pour restreindre
 * l'accès aux films selon les festivals assignés à l'utilisateur connecté.
 * 
 * Fonctionnalités de sécurité :
 * - Les utilisateurs non-admin ne voient que les films des festivals auxquels ils ont accès
 * - Les permissions CRUD vérifient l'accès au festival avant autorisation
 * - Les filtres et options sont limités aux festivals accessibles
 * - Les super_admin et admin ont un accès complet sans restrictions
 */
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
     * Contrôler si la ressource est visible dans la navigation
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Vérifier si l'utilisateur peut accéder à cette ressource
     * Utilise les permissions Shield générées automatiquement
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        
        if (! $user) {
            return false;
        }
        
        // Vérifier la permission Shield pour cette ressource
        // Shield utilise la syntaxe ViewAny:Movie, pas view_any_movie
        return $user->can('ViewAny:Movie') || 
               $user->hasAnyRole(['super_admin', 'admin']); // Super admin toujours autorisé
    }

    /**
     * Permissions CRUD avec Shield
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user && (
            $user->can('Create:Movie') || 
            $user->hasAnyRole(['super_admin', 'admin'])
        );
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Vérifier d'abord les permissions Shield de base
        $hasPermission = $user->can('Update:Movie') || $user->hasAnyRole(['super_admin', 'admin']);
        
        if (!$hasPermission) {
            return false;
        }
        
        // Si super admin ou admin, pas de restriction de festival
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        // Vérifier l'accès au festival pour ce film spécifique
        $festivalAccessService = app(FestivalAccessService::class);
        return $festivalAccessService->canAccessMovie($user, $record);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Vérifier d'abord les permissions Shield de base
        $hasPermission = $user->can('Delete:Movie') || $user->hasAnyRole(['super_admin', 'admin']);
        
        if (!$hasPermission) {
            return false;
        }
        
        // Si super admin ou admin, pas de restriction de festival
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        // Vérifier l'accès au festival pour ce film spécifique
        $festivalAccessService = app(FestivalAccessService::class);
        return $festivalAccessService->canAccessMovie($user, $record);
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Vérifier d'abord les permissions Shield de base
        $hasPermission = $user->can('View:Movie') || $user->hasAnyRole(['super_admin', 'admin']);
        
        if (!$hasPermission) {
            return false;
        }
        
        // Si super admin ou admin, pas de restriction de festival
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }
        
        // Vérifier l'accès au festival pour ce film spécifique
        $festivalAccessService = app(FestivalAccessService::class);
        return $festivalAccessService->canAccessMovie($user, $record);
    }

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

                    // Étape 2 : Versions et Paramètres (REFACTORISÉ)
                    Step::make('Versions et Paramètres')
                        ->description('Configurer les paramètres du film pour générer la version')
                        ->icon('heroicon-o-cube')
                        ->schema([
                            // 0. Titre récupéré du Step 1 - Affiché comme header
                            \Filament\Forms\Components\Placeholder::make('title_from_step1')
                                ->hiddenLabel()
                                ->content(fn ($get) => new \Illuminate\Support\HtmlString('<h2 class="text-2xl font-bold text-primary-600 mb-4">' . ($get('title') ?: 'Aucun titre saisi') . '</h2>'))
                                ->columnSpanFull(),

                            // 1. Paramètres du festival pour la création
                            Section::make('Paramètres du Film')
                                ->description('Ces paramètres détermineront les caractéristiques de la version générée')
                                ->schema(
                                    static::getMovieFormService()->buildVersionParametersFields()
                                )
                                ->columns(2)
                                ->visible(fn ($operation) => $operation === 'create'),

                            // 2. Prévisualisation en temps réel - Version PHP réactive
                            \Filament\Forms\Components\Placeholder::make('nomenclature_preview')
                                ->label('Nomenclature générée')
                                ->content(function ($get) {
                                    $title = $get('title');
                                    
                                    if (empty($title)) {
                                        return 'Veuillez saisir le titre du film';
                                    }
                                    
                                    // Appeler directement le service de nomenclature
                                    $movieFormService = static::getMovieFormService();
                                    $nomenclature = $movieFormService->generateRealtimeNomenclature($get);
                                    
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="bg-gray-50 rounded-lg p-4 border">' .
                                        '<div class="text-lg font-mono font-bold text-gray-900">' . $nomenclature . '</div>' .
                                        '</div>'
                                    );
                                })
                                ->live()
                                ->columnSpanFull()
                                ->visible(fn ($operation) => $operation === 'create'),

                            // 3. Tableau des versions existantes pour l'édition
                            ViewField::make('versions_table')
                                ->view('fresnel::filament.forms.components.versions-table')
                                ->viewData(fn ($record) => [
                                    'versions' => $record ? $record->versions()->with(['movie'])->get() : collect(),
                                    'operation' => 'edit',
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
                    
                SelectFilter::make('festival')
                    ->label('Festival')
                    ->relationship('festivals', 'name')
                    ->options(function () {
                        $user = auth()->user();
                        
                        if (!$user) {
                            return [];
                        }
                        
                        // Si admin ou super_admin, voir tous les festivals
                        if ($user->hasAnyRole(['super_admin', 'admin'])) {
                            return \Modules\Fresnel\app\Models\Festival::pluck('name', 'id')->toArray();
                        }
                        
                        // Sinon, seulement les festivals accessibles à l'utilisateur
                        return $user->festivals()->pluck('name', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload(),
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

                            $message = match ($record->status) {
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
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Filtrer les films selon les festivals accessibles à l'utilisateur et festival sélectionné
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Si l'utilisateur est super admin ou admin, respecter quand même la sélection de festival
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $selectedFestivalId = Session::get('selected_festival_id');
            
            if (!$selectedFestivalId) {
                return parent::getEloquentQuery();
            }
            
            return parent::getEloquentQuery()
                ->whereHas('festivals', function (Builder $query) use ($selectedFestivalId) {
                    $query->where('festival_id', $selectedFestivalId);
                });
        }

        // Pour les autres utilisateurs : combiner les restrictions
        $selectedFestivalId = Session::get('selected_festival_id');
        $query = FestivalAccessService::applyFestivalScope(parent::getEloquentQuery());
        
        // Si un festival spécifique est sélectionné, le filtrer en plus
        if ($selectedFestivalId) {
            // Vérifier que l'utilisateur a accès à ce festival
            if ($user->festivals()->where('festivals.id', $selectedFestivalId)->exists()) {
                $query = $query->whereHas('festivals', function (Builder $subQuery) use ($selectedFestivalId) {
                    $subQuery->where('festival_id', $selectedFestivalId);
                });
            } else {
                // Festival sélectionné non autorisé pour cet utilisateur
                return parent::getEloquentQuery()->whereRaw('1 = 0');
            }
        }
        
        return $query;
    }

    /**
     * Hook avant création : associer au festival et créer le compte Source
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \Exception('Utilisateur non authentifié');
        }
        
        // Vérifier qu'un festival est sélectionné
        $festival = static::getFestivalContextService()->requireFestivalSelected();
        
        // Vérifier que l'utilisateur peut créer des films pour ce festival (sauf admin/super_admin)
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $festivalAccessService = app(FestivalAccessService::class);
            if (!$festivalAccessService->canAccessFestival($user, $festival)) {
                throw new \Exception("Vous n'avez pas accès à ce festival : {$festival->name}");
            }
        }

        // Créer ou récupérer l'utilisateur Source
        $sourceUser = static::getOrCreateSourceUser($data['source_email']);

        // Le film sera associé au festival après création via l'event created
        $data['created_by'] = auth()->id();
        $data['status'] = Movie::STATUS_FILM_CREATED;

        return $data;
    }

    /**
     * Hook après création : créer les associations, paramètres et versions (REFACTORISÉ)
     */
    public static function afterCreate($record, array $data): void
    {
        $movieFormService = static::getMovieFormService();
        $versionService = app(\Modules\Fresnel\app\Services\VersionManagement\MovieVersionService::class);

        DB::transaction(function () use ($record, $data, $movieFormService, $versionService) {
            // Vérifier qu'un festival est sélectionné
            $festival = static::getFestivalContextService()->requireFestivalSelected();

            // 1. Associer le film aux festivals sélectionnés
            $movieFormService->associateMovieToFestivals($record, $data);

            // 2. Créer les paramètres du festival pour le film
            $movieFormService->createMovieParametersFromFormData($record, $data);

            // 3. Créer UNE SEULE version basée sur la configuration festival
            try {
                // Extraire les valeurs de paramètres depuis les données du formulaire
                $parameterValues = static::extractParameterValuesFromFormData($data);

                // Créer la version en utilisant le nouveau service
                $version = $versionService->createVersionForMovie(
                    $record,
                    $festival,
                    $parameterValues
                );

                Log::info('Version created successfully', [
                    'movie_id' => $record->id,
                    'version_id' => $version->id,
                    'nomenclature' => $version->generated_nomenclature,
                    'type' => $version->type,
                ]);

                Notification::make()
                    ->title('Version créée avec succès')
                    ->body("Version '{$version->generated_nomenclature}' créée pour le film '{$record->title}'.")
                    ->success()
                    ->send();

            } catch (\Exception $e) {
                Log::error('Failed to create version for movie', [
                    'movie_id' => $record->id,
                    'festival_id' => $festival->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Créer une version de fallback minimale
                $fallbackVersion = \Modules\Fresnel\app\Models\Version::create([
                    'movie_id' => $record->id,
                    'type' => 'VO',
                    'audio_lang' => 'original',
                    'sub_lang' => null,
                    'accessibility' => null,
                    'format' => 'FTR',
                    'generated_nomenclature' => $record->title.'_VO_FALLBACK',
                ]);

                Notification::make()
                    ->title('Version de secours créée')
                    ->body('Impossible de générer la version selon la configuration. Version par défaut créée.')
                    ->warning()
                    ->send();
            }
        });
    }

    /**
     * Extraire les valeurs de paramètres depuis les données du formulaire
     */
    protected static function extractParameterValuesFromFormData(array $data): array
    {
        $parameterValues = [];
        
        // Extraire tous les champs parameter_*
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'parameter_')) {
                $parameterId = str_replace('parameter_', '', $key);
                // On garde l'ID pour l'instant, le service se chargera du mapping
                $parameterValues[$parameterId] = $value;
            }
        }
        
        return $parameterValues;
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
            if (! $existingUser->hasRole('source')) {
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
            ->body("Nouveau compte Source créé pour {$email}. ".($emailSent ? 'Email envoyé.' : 'Erreur envoi email.'))
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
