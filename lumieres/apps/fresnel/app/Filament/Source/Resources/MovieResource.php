<?php

namespace App\Filament\Source\Resources;

use App\Models\Movie;
use App\Models\Version;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Mes Films';
    
    protected static ?string $modelLabel = 'Film';
    
    protected static ?string $pluralModelLabel = 'Mes Films';
    
    protected static ?int $navigationSort = 1;
    
    protected static string|UnitEnum|null $navigationGroup = 'Upload DCP';

    public static function form(Schema $schema): Schema
    {
        // Les Sources ne peuvent pas modifier les films, seulement les voir
        return $schema
            ->schema([
                Section::make('Informations du Film')
                    ->description('Détails du film demandé par le festival')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre du Film')
                            ->disabled()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('duration')
                            ->label('Durée')
                            ->disabled()
                            ->suffix(' minutes'),

                        TextInput::make('genre')
                            ->label('Genre')
                            ->disabled(),

                        TextInput::make('year')
                            ->label('Année')
                            ->disabled(),

                        TextInput::make('country')
                            ->label('Pays d\'origine')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Versions Demandées')
                    ->description('Versions DCP à fournir')
                    ->icon('heroicon-o-language')
                    ->schema([
                        TextInput::make('expected_versions')
                            ->label('Versions Attendues')
                            ->disabled()
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),

                        Select::make('format')
                            ->label('Format DCP Requis')
                            ->disabled()
                            ->options([
                                '2K' => '2K (2048×1080)',
                                '4K' => '4K (4096×2160)',
                                'HD' => 'HD (1920×1080)',
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre du Film')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                BadgeColumn::make('status')
                    ->label('Statut Upload')
                    ->colors([
                        'gray' => Movie::STATUS_PENDING,
                        'warning' => Movie::STATUS_TOKEN_SENT,
                        'info' => Movie::STATUS_UPLOADING,
                        'success' => Movie::STATUS_UPLOAD_OK,
                        'danger' => Movie::STATUS_UPLOAD_NOK,
                        'primary' => Movie::STATUS_IN_REVIEW,
                        'success' => Movie::STATUS_VALIDATED,
                        'danger' => Movie::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn ($state) => Movie::getStatuses()[$state] ?? $state),

                TextColumn::make('expected_versions')
                    ->label('Versions à Fournir')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(40),

                TextColumn::make('format')
                    ->label('Format DCP')
                    ->badge()
                    ->color('info'),

                TextColumn::make('festivals_count')
                    ->label('Festivals')
                    ->counts('festivals')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('versions_count')
                    ->label('Versions Créées')
                    ->counts('versions')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Demandé le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Movie::getStatuses()),

                SelectFilter::make('format')
                    ->label('Format DCP')
                    ->options([
                        '2K' => '2K',
                        '4K' => '4K',
                        'HD' => 'HD',
                    ]),
            ])
            ->actions([
                Action::make('manage_versions')
                    ->label('Gérer DCPs')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('primary')
                    ->url(fn (Movie $record) => route('filament.source.resources.movies.manage-dcps', $record))
                    ->visible(fn (Movie $record) => !in_array($record->status, [Movie::STATUS_VALIDATED, Movie::STATUS_REJECTED])),
                    
                ViewAction::make()
                    ->label('Détails'),
            ])
            ->bulkActions([
                // Pas d'actions en masse pour les Sources
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun film assigné')
            ->emptyStateDescription('Vous n\'avez actuellement aucun film à traiter. Les festivals vous assigneront des films via votre email.')
            ->emptyStateIcon('heroicon-o-film');
    }

    /**
     * Filtrer les films pour la Source connectée
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('source_email', auth()->user()->email)
            ->with(['festivals', 'versions']);
    }

    public static function getPages(): array
    {
        return [
            'index' => MovieResource\Pages\ListMovies::route('/'),
            'view' => MovieResource\Pages\ViewMovie::route('/{record}'),
            'manage-dcps' => MovieResource\Pages\ManageDcps::route('/{record}/manage-dcps'),
        ];
    }
}
