<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Filament\Resources\Versions\Tables\VersionTable;
use Modules\Fresnel\app\Models\Version;
use Modules\Fresnel\app\Services\FestivalAccessService;
use UnitEnum;

class VersionResource extends Resource
{
    protected static ?string $model = Version::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $navigationLabel = 'Versions';

    protected static ?string $modelLabel = 'Version';

    protected static ?string $pluralModelLabel = 'Versions';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Gestion Festival';

    /**
     * Configure the table with manager-specific adaptations
     */
    public static function table(Table $table): Table
    {
        // Start with the existing table configuration
        $configuredTable = VersionTable::configure($table);

        // Override record actions for manager-specific functionality
        return $configuredTable
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir'),
                    EditAction::make()
                        ->label('Éditer')
                        ->visible(fn (Version $record): bool => static::canEditVersion($record)
                        ),
                    Action::make('generate_nomenclature')
                        ->label('Générer Nomenclature')
                        ->icon('heroicon-o-sparkles')
                        ->color('success')
                        ->visible(fn (Version $record): bool => static::canEditVersion($record)
                        )
                        ->action(function (Version $record) {
                            static::generateVersionNomenclature($record);
                        }),
                    Action::make('preview_nomenclature')
                        ->label('Aperçu Nomenclature')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading('Aperçu des nomenclatures')
                        ->modalContent(function (Version $record) {
                            return static::getNomenclaturePreview($record);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fermer'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    /**
     * Filter versions according to user's accessible festivals and selected festival
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
                return parent::getEloquentQuery()->whereRaw('1 = 0');
            }
            
            return parent::getEloquentQuery()
                ->whereHas('movie.festivals', function (Builder $query) use ($selectedFestivalId) {
                    $query->where('festival_id', $selectedFestivalId);
                })
                ->with(['movie', 'dcps', 'audioLanguage', 'subtitleLanguage']);
        }

        // Pour les autres utilisateurs : combiner les restrictions
        $selectedFestivalId = Session::get('selected_festival_id');
        $query = FestivalAccessService::applyFestivalScope(parent::getEloquentQuery());
        
        // Si un festival spécifique est sélectionné, le filtrer en plus
        if ($selectedFestivalId) {
            // Vérifier que l'utilisateur a accès à ce festival
            if ($user->festivals()->where('festivals.id', $selectedFestivalId)->exists()) {
                $query = $query->whereHas('movie.festivals', function (Builder $subQuery) use ($selectedFestivalId) {
                    $subQuery->where('festival_id', $selectedFestivalId);
                });
            } else {
                // Festival sélectionné non autorisé pour cet utilisateur
                return parent::getEloquentQuery()->whereRaw('1 = 0');
            }
        }
        
        return $query->with(['movie', 'dcps', 'audioLanguage', 'subtitleLanguage']);
    }

    /**
     * Check if manager can edit this version
     */
    protected static function canEditVersion(Version $version): bool
    {
        $user = auth()->user();
        $festivalId = Session::get('selected_festival_id');

        if (! $user || ! $user->hasRole('manager') || ! $festivalId) {
            return false;
        }

        // Vérifier que la version appartient à un film du festival du manager
        return $version->movie->festivals()->where('festival_id', $festivalId)->exists();
    }

    /**
     * Generate nomenclature for a version
     */
    protected static function generateVersionNomenclature(Version $record): void
    {
        try {
            $nomenclatureService = app(\Modules\Fresnel\app\Services\UnifiedNomenclatureService::class);

            // Générer la nomenclature pour le festival du manager
            $movie = $record->movie;
            $festivalId = Session::get('selected_festival_id');

            if (! $festivalId) {
                throw new \Exception('Aucun festival sélectionné');
            }

            $festival = $movie->festivals()->where('festival_id', $festivalId)->first();

            if (! $festival) {
                throw new \Exception('Ce film n\'est pas associé au festival sélectionné');
            }

            $nomenclature = $nomenclatureService->generateMovieNomenclature($movie, $festival);

            // Mettre à jour la version
            $record->update([
                'generated_nomenclature' => $nomenclature,
            ]);

            Notification::make()
                ->title('Nomenclature générée avec succès')
                ->body('Nomenclature: '.$nomenclature)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de génération')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Get nomenclature preview for modal
     */
    protected static function getNomenclaturePreview(Version $record)
    {
        try {
            $nomenclatureService = app(\Modules\Fresnel\app\Services\UnifiedNomenclatureService::class);
            $movie = $record->movie;
            $festivalId = Session::get('selected_festival_id');

            if (! $festivalId) {
                return view('filament.modals.version-nomenclature-preview', [
                    'message' => 'Aucun festival sélectionné',
                ]);
            }

            $festival = $movie->festivals()->where('festival_id', $festivalId)->first();

            if (! $festival) {
                return view('filament.modals.version-nomenclature-preview', [
                    'message' => 'Ce film n\'est pas associé au festival sélectionné',
                ]);
            }

            $preview = $nomenclatureService->previewNomenclature($movie, $festival);

            return view('filament.modals.version-nomenclature-preview', [
                'previews' => [
                    [
                        'festival' => $festival->name,
                        'preview' => $preview,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return view('filament.modals.version-nomenclature-preview', [
                'message' => 'Erreur: '.$e->getMessage(),
            ]);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Fresnel\app\Filament\Manager\Resources\VersionResource\Pages\ListVersions::route('/'),
        ];
    }
}
