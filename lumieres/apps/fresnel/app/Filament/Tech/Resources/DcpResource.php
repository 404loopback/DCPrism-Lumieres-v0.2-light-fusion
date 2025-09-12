<?php

namespace App\Filament\Tech\Resources;

use App\Models\Dcp;
use App\Models\Movie;
use App\Models\Version;
use App\Models\Festival;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use BackedEnum;

class DcpResource extends Resource
{
    protected static ?string $model = Dcp::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Validation DCPs';
    
    protected static ?string $modelLabel = 'DCP';
    
    protected static ?string $pluralModelLabel = 'DCPs à Valider';
    
    protected static ?int $navigationSort = 1;
    
    protected static string|UnitEnum|null $navigationGroup = 'Validation Technique';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations DCP')
                    ->description('Détails du fichier DCP uploadé')
                    ->icon('heroicon-o-film')
                    ->schema([
                        TextInput::make('movie.title')
                            ->label('Film')
                            ->disabled()
                            ->columnSpanFull(),

                        TextInput::make('version.generated_nomenclature')
                            ->label('Version')
                            ->disabled(),

                        Select::make('status')
                            ->label('Statut de Validation')
                            ->options(Dcp::STATUSES)
                            ->required()
                            ->native(false),

                        TextInput::make('file_size')
                            ->label('Taille du Fichier')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? number_format($state / (1024 * 1024 * 1024), 2) . ' GB' : 'Inconnu'),

                        DateTimePicker::make('uploaded_at')
                            ->label('Uploadé le')
                            ->disabled(),

                        DateTimePicker::make('validated_at')
                            ->label('Validé le')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Validation Technique')
                    ->description('Contrôle qualité et validation')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Toggle::make('is_valid')
                            ->label('DCP Valide')
                            ->helperText('Marquer ce DCP comme techniquement valide'),

                        Textarea::make('validation_notes')
                            ->label('Notes de Validation')
                            ->placeholder('Commentaires sur la validation technique...')
                            ->rows(4)
                            ->columnSpanFull(),

                        KeyValue::make('technical_metadata')
                            ->label('Métadonnées Techniques')
                            ->keyLabel('Paramètre')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter un paramètre')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informations Contextuelles')
                    ->description('Contexte du film et festival')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('movie.source_email')
                            ->label('Source Email')
                            ->disabled(),

                        TextInput::make('movie.format')
                            ->label('Format Demandé')
                            ->disabled(),

                        TextInput::make('uploader.name')
                            ->label('Uploadé par')
                            ->disabled(),

                        TextInput::make('festivals_count')
                            ->label('Festivals Concernés')
                            ->disabled()
                            ->formatStateUsing(fn ($record) => $record?->movie?->festivals?->count() ?? 0),
                    ])->columns(2),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('movie.title')
                    ->label('Film')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),

                TextColumn::make('version.generated_nomenclature')
                    ->label('Version')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->version?->generated_nomenclature),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => Dcp::STATUS_UPLOADED,
                        'info' => Dcp::STATUS_PROCESSING,
                        'success' => Dcp::STATUS_VALID,
                        'danger' => Dcp::STATUS_INVALID,
                        'gray' => Dcp::STATUS_ERROR,
                    ])
                    ->formatStateUsing(fn ($state) => Dcp::STATUSES[$state] ?? $state),

                IconColumn::make('is_valid')
                    ->label('Valide')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('file_size')
                    ->label('Taille')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / (1024 * 1024 * 1024), 1) . ' GB' : '-')
                    ->sortable(),

                TextColumn::make('movie.source_email')
                    ->label('Source')
                    ->searchable()
                    ->limit(20)
                    ->copyable(),

                TextColumn::make('uploaded_at')
                    ->label('Uploadé')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),

                TextColumn::make('validated_at')
                    ->label('Validé')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                BadgeColumn::make('priority')
                    ->label('Priorité')
                    ->formatStateUsing(function ($record) {
                        $pendingCount = $record->movie?->festivals?->sum(fn ($festival) => 
                            $festival->getStats()['pending_movies'] ?? 0
                        );
                        
                        if ($pendingCount > 10) return 'Haute';
                        if ($pendingCount > 5) return 'Moyenne';
                        return 'Normale';
                    })
                    ->colors([
                        'danger' => fn ($state) => $state === 'Haute',
                        'warning' => fn ($state) => $state === 'Moyenne',
                        'success' => fn ($state) => $state === 'Normale',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Dcp::STATUSES)
                    ->default(Dcp::STATUS_UPLOADED),

                TernaryFilter::make('is_valid')
                    ->label('Validation')
                    ->placeholder('Tous')
                    ->trueLabel('Validés uniquement')
                    ->falseLabel('Non validés uniquement'),

                Filter::make('uploaded_recently')
                    ->label('Uploadé récemment')
                    ->query(fn (Builder $query) => $query->where('uploaded_at', '>=', now()->subDays(3))),

                SelectFilter::make('movie.source_email')
                    ->label('Source')
                    ->relationship('movie', 'source_email')
                    ->searchable()
                    ->multiple(),
            ])
            ->actions([
                Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider le DCP')
                    ->modalDescription('Confirmer que ce DCP est techniquement valide ?')
                    ->action(function (Dcp $record) {
                        $record->markAsValid('DCP validé par technicien le ' . now()->format('d/m/Y H:i'));
                        
                        // Mettre à jour le statut du film si nécessaire
                        static::updateMovieStatus($record->movie);
                        
                        Notification::make()
                            ->title('DCP validé avec succès')
                            ->body("Le DCP {$record->version?->generated_nomenclature} a été marqué comme valide.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Dcp $record) => !$record->is_valid),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->modalHeading('Rejeter le DCP')
                    ->modalDescription('Veuillez indiquer la raison du rejet de ce DCP.')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motif de Rejet')
                            ->required()
                            ->placeholder('Expliquez pourquoi ce DCP est rejeté...')
                            ->rows(3),
                    ])
                    ->action(function (Dcp $record, array $data) {
                        $record->markAsInvalid($data['rejection_reason']);
                        
                        Notification::make()
                            ->title('DCP rejeté')
                            ->body("Le DCP {$record->version?->generated_nomenclature} a été rejeté.")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Dcp $record) => $record->status !== Dcp::STATUS_INVALID),

                ViewAction::make()
                    ->label('Examiner'),
            ])
            ->bulkActions([
                BulkAction::make('validate_selected')
                    ->label('Valider Sélection')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider les DCPs sélectionnés')
                    ->modalDescription('Confirmer la validation de tous les DCPs sélectionnés ?')
                    ->action(function (Collection $records) {
                        $count = 0;
                        
                        foreach ($records as $record) {
                            if (!$record->is_valid) {
                                $record->markAsValid('DCP validé en masse par technicien le ' . now()->format('d/m/Y H:i'));
                                static::updateMovieStatus($record->movie);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title('Validation en masse terminée')
                            ->body("{$count} DCPs ont été validés avec succès.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteBulkAction::make()
                    ->label('Supprimer Sélection'),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->poll('30s') // Actualisation auto toutes les 30s
            ->emptyStateHeading('Aucun DCP à valider')
            ->emptyStateDescription('Tous les DCPs ont été traités ou aucun DCP n\'a été uploadé récemment.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }

    /**
     * Mettre à jour le statut du film selon les DCPs validés
     */
    protected static function updateMovieStatus(Movie $movie): void
    {
        $totalDcps = $movie->dcps()->count();
        $validDcps = $movie->dcps()->where('is_valid', true)->count();
        $invalidDcps = $movie->dcps()->where('is_valid', false)->count();
        
        if ($validDcps > 0 && $invalidDcps === 0) {
            // Tous les DCPs sont validés
            $movie->update([
                'status' => Movie::STATUS_VALIDATED,
                'validated_at' => now(),
                'validated_by' => auth()->id(),
            ]);
        } elseif ($invalidDcps > 0) {
            // Au moins un DCP invalide
            $movie->update(['status' => Movie::STATUS_REJECTED]);
        }
    }

    /**
     * Filtrer les DCPs pour les festivals assignés au technicien
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['movie.festivals', 'version', 'uploader']);
            
        // Si le technicien est assigné à des festivals spécifiques
        $user = auth()->user();
        if ($user->festivals()->exists()) {
            $festivalIds = $user->festivals()->pluck('festivals.id');
            
            $query->whereHas('movie.festivals', function (Builder $subQuery) use ($festivalIds) {
                $subQuery->whereIn('festivals.id', $festivalIds);
            });
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => DcpResource\Pages\ListDcps::route('/'),
            'view' => DcpResource\Pages\ViewDcp::route('/{record}'),
            'edit' => DcpResource\Pages\EditDcp::route('/{record}/edit'),
        ];
    }
}
