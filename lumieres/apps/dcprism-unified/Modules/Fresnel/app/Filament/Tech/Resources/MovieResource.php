<?php

namespace Modules\Fresnel\app\Filament\Tech\Resources;

use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Dcp;
use Modules\Fresnel\app\Models\Movie;
use UnitEnum;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Films & Statuts';

    protected static ?string $modelLabel = 'Film';

    protected static ?string $pluralModelLabel = 'Films';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Validation Technique';

    public static function form(Schema $schema): Schema
    {
        // Vue en lecture seule pour les techniciens
        return $schema
            ->schema([
                Section::make('Informations du Film')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->disabled(),

                        TextInput::make('source_email')
                            ->label('Source')
                            ->disabled(),

                        TextInput::make('status')
                            ->label('Statut')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => Movie::getStatuses()[$state] ?? $state),

                        Textarea::make('description')
                            ->label('Description')
                            ->disabled()
                            ->rows(3),
                    ])->columns(2),

                Section::make('Informations Techniques')
                    ->schema([
                        TextInput::make('format')
                            ->label('Format DCP')
                            ->disabled(),

                        TextInput::make('duration')
                            ->label('Durée')
                            ->disabled()
                            ->suffix(' min'),

                        TextInput::make('expected_versions')
                            ->label('Versions Attendues')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
                    ])->columns(3),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Film')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                BadgeColumn::make('status')
                    ->label('Statut Global')
                    ->colors([
                        'gray' => Movie::STATUS_FILM_CREATED,
                        'warning' => Movie::STATUS_SOURCE_VALIDATED,
                        'info' => Movie::STATUS_VERSIONS_VALIDATED,
                        'primary' => Movie::STATUS_UPLOADS_OK,
                        'warning' => Movie::STATUS_UPLOAD_ERROR,
                        'success' => Movie::STATUS_VALIDATION_OK,
                        'danger' => Movie::STATUS_VALIDATION_ERROR,
                    ])
                    ->formatStateUsing(fn ($state) => Movie::getStatuses()[$state] ?? $state),

                TextColumn::make('dcps_count')
                    ->label('DCPs')
                    ->counts('dcps')
                    ->badge()
                    ->color('info'),

                TextColumn::make('validated_dcps_count')
                    ->label('Validés')
                    ->formatStateUsing(function ($record) {
                        return $record->dcps()->where('is_valid', true)->count();
                    })
                    ->badge()
                    ->color('success'),

                TextColumn::make('pending_dcps_count')
                    ->label('En Attente')
                    ->formatStateUsing(function ($record) {
                        return $record->dcps()->where('status', Dcp::STATUS_UPLOADED)->count();
                    })
                    ->badge()
                    ->color('warning'),

                TextColumn::make('source_email')
                    ->label('Source')
                    ->searchable()
                    ->limit(25)
                    ->copyable(),

                TextColumn::make('expected_versions')
                    ->label('Versions')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(30),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut Global')
                    ->options(Movie::getStatuses()),

                Filter::make('has_pending_dcps')
                    ->label('Avec DCPs en Attente')
                    ->query(fn (Builder $query) => $query->whereHas('dcps', fn ($q) => $q->where('status', Dcp::STATUS_UPLOADED))),

                Filter::make('fully_validated')
                    ->label('Entièrement Validés')
                    ->query(fn (Builder $query) => $query->where('status', Movie::STATUS_VALIDATION_OK)),

                SelectFilter::make('source_email')
                    ->label('Source')
                    ->options(fn () => Movie::distinct('source_email')->pluck('source_email', 'source_email')->toArray())
                    ->searchable()
                    ->multiple(),
            ])
            ->actions([
                Action::make('validate_all_dcps')
                    ->label('Valider Tous les DCPs')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider tous les DCPs du film')
                    ->modalDescription(function ($record) {
                        $pendingCount = $record->dcps()->where('status', Dcp::STATUS_UPLOADED)->count();

                        return "Valider les {$pendingCount} DCPs en attente pour ce film ?";
                    })
                    ->action(function (Movie $record) {
                        $pendingDcps = $record->dcps()
                            ->where('status', Dcp::STATUS_UPLOADED)
                            ->where('is_valid', false)
                            ->get();

                        $count = 0;
                        foreach ($pendingDcps as $dcp) {
                            $dcp->markAsValid('DCP validé par technicien le '.now()->format('d/m/Y H:i'));
                            $count++;
                        }

                        // Mettre à jour le statut global du film
                        if ($count > 0) {
                            $record->update([
                                'status' => Movie::STATUS_VALIDATION_OK,
                                'validated_at' => now(),
                                'validated_by' => auth()->id(),
                            ]);
                        }

                        Notification::make()
                            ->title('Validation terminée')
                            ->body("{$count} DCPs validés pour le film {$record->title}")
                            ->success()
                            ->send();
                    })
                    ->visible(function (Movie $record) {
                        return $record->dcps()->where('status', Dcp::STATUS_UPLOADED)->count() > 0;
                    }),

                Action::make('view_dcps')
                    ->label('Voir DCPs')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(function (Movie $record) {
                        return route('filament.tech.resources.dcps.index', [
                            'tableFilters' => [
                                'movie' => ['value' => $record->id],
                            ],
                        ]);
                    }),

                ViewAction::make()
                    ->label('Détails'),
            ])
            ->bulkActions([
                // Pas d'actions en masse pour éviter les erreurs
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun film à valider')
            ->emptyStateDescription('Aucun film n\'a été créé ou tous ont été traités.')
            ->emptyStateIcon('heroicon-o-film');
    }

    /**
     * Filtrer les films pour les festivals assignés au technicien
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['dcps', 'festivals']);

        // Si le technicien est assigné à des festivals spécifiques
        $user = auth()->user();
        if ($user->festivals()->exists()) {
            $festivalIds = $user->festivals()->pluck('festivals.id');

            $query->whereHas('festivals', function (Builder $subQuery) use ($festivalIds) {
                $subQuery->whereIn('festivals.id', $festivalIds);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => MovieResource\Pages\ListMovies::route('/'),
            'view' => MovieResource\Pages\ViewMovie::route('/{record}'),
        ];
    }
}
