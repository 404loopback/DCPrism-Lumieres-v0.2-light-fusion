<?php

namespace Modules\Fresnel\app\Filament\Resources\Movies\Schemas;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Version;
use Modules\Fresnel\app\Models\Dcp;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ViewEntry;

class MovieInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Titre')
                                    ->weight('medium'),
                                    
                                TextEntry::make('format')
                                    ->label('Format')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'FTR' => 'primary',
                                        'SHR' => 'success',
                                        'TRL' => 'warning',
                                        'TST' => 'danger',
                                        default => 'gray'
                                    }),
                                    
                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        Movie::STATUS_FILM_CREATED => 'gray',
                                        Movie::STATUS_SOURCE_VALIDATED => 'info',
                                        Movie::STATUS_VERSIONS_VALIDATED, Movie::STATUS_UPLOADS_OK, Movie::STATUS_VALIDATION_OK, Movie::STATUS_DISTRIBUTION_OK => 'success',
                                        Movie::STATUS_VERSIONS_REJECTED, Movie::STATUS_UPLOAD_ERROR, Movie::STATUS_VALIDATION_ERROR, Movie::STATUS_DISTRIBUTION_ERROR => 'danger',
                                        default => 'gray'
                                    })
                                    ->formatStateUsing(fn (string $state): string => Movie::getStatuses()[$state] ?? $state),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('source_email')
                                    ->label('Source')
                                    ->copyable(),
                                    
                                TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime('d/m/Y à H:i'),
                            ]),
                    ]),
                    
                Section::make('Validation Source')
                    ->description('Gestion des versions par la source')
                    ->schema([
                        ViewEntry::make('versions_table')
                            ->label('')
                            ->view('filament.infolists.versions-table')
                            ->state(function (Movie $record): array {
                                return [
                                    'movie_id' => $record->id,
                                    'versions' => $record->versions()->with(['audioLanguage', 'subtitleLanguage', 'dcps'])->get()
                                ];
                            }),
                    ])
                    ->collapsible(),
                    
                Section::make('Validation Technique')
                    ->description('Gestion des DCP par les techniciens')
                    ->schema([
                        ViewEntry::make('dcps_table')
                            ->label('')
                            ->view('filament.infolists.dcps-table')
                            ->state(function (Movie $record): array {
                                return [
                                    'movie_id' => $record->id,
                                    'dcps' => $record->dcps()->with(['version', 'uploader', 'audioLanguage', 'subtitleLanguage'])->get()
                                ];
                            }),
                    ])
                    ->collapsible(),
                    
                Section::make('Festivals liés')
                    ->description('Informations sur les soumissions aux festivals')
                    ->schema([
                        RepeatableEntry::make('festivals')
                            ->label('')
                            ->getStateUsing(function (Movie $record): array {
                                return $record->festivals()->withPivot(['submission_status', 'selected_versions', 'technical_notes', 'priority'])->get()
                                    ->map(function ($festival) {
                                        return [
                                            'name' => $festival->name,
                                            'submission_status' => $festival->pivot->submission_status,
                                            'priority' => $festival->pivot->priority,
                                            'technical_notes' => $festival->pivot->technical_notes,
                                            'selected_versions' => $festival->pivot->selected_versions,
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Festival')
                                            ->weight('medium'),
                                            
                                        TextEntry::make('submission_status')
                                            ->label('Statut')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending' => 'gray',
                                                'submitted' => 'warning',
                                                'in_review' => 'info',
                                                'accepted' => 'success',
                                                'rejected' => 'danger',
                                                'withdrawn' => 'secondary',
                                                default => 'gray'
                                            })
                                            ->formatStateUsing(fn (string $state): string => match($state) {
                                                'pending' => 'En attente',
                                                'submitted' => 'Soumis',
                                                'in_review' => 'En cours d\'examen',
                                                'accepted' => 'Accepté',
                                                'rejected' => 'Rejeté',
                                                'withdrawn' => 'Retiré',
                                                default => 'Inconnu'
                                            }),
                                            
                                        TextEntry::make('priority')
                                            ->label('Priorité')
                                            ->badge()
                                            ->color(fn (int $state): string => match ($state) {
                                                0, 1 => 'gray',
                                                2 => 'warning',
                                                3, 4, 5 => 'danger',
                                                default => 'gray'
                                            })
                                            ->formatStateUsing(fn (int $state): string => match($state) {
                                                0 => 'Normale',
                                                1 => 'Faible',
                                                2 => 'Moyenne',
                                                3 => 'Haute',
                                                4 => 'Critique',
                                                5 => 'Urgente',
                                                default => 'Inconnue'
                                            }),
                                            
                                        TextEntry::make('technical_notes')
                                            ->label('Notes')
                                            ->placeholder('Aucune note')
                                            ->limit(50),
                                    ]),
                            ]),
                    ])
                    ->visible(fn (Movie $record): bool => $record->festivals()->exists())
                    ->collapsible(),
                    
                Section::make('Métadonnées')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('year')
                                    ->label('Année')
                                    ->placeholder('Non renseignée'),
                                    
                                TextEntry::make('duration')
                                    ->label('Durée')
                                    ->suffix(' min')
                                    ->placeholder('Non renseignée'),
                                    
                                TextEntry::make('country')
                                    ->label('Pays')
                                    ->placeholder('Non renseigné'),
                            ]),
                            
                        TextEntry::make('description')
                            ->label('Description')
                            ->prose()
                            ->placeholder('Aucune description')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
