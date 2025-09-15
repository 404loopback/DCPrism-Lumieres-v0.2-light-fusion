<?php

namespace Modules\Fresnel\app\Filament\Resources\Dcps\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;

class DcpInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de base')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('movie.title')
                                    ->label('Film')
                                    ->weight('bold'),
                                    
                                TextEntry::make('version.type')
                                    ->label('Version')
                                    ->badge()
                                    ->color('primary'),
                                    
                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => \Modules\Fresnel\app\Models\Dcp::STATUSES[$state] ?? $state)
                                    ->color(fn ($state) => match($state) {
                                        'valid' => 'success',
                                        'invalid' => 'danger',
                                        'processing' => 'warning',
                                        default => 'gray'
                                    }),
                            ]),
                    ]),
                    
                Section::make('Fichier')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('formatted_file_size')
                                    ->label('Taille du fichier'),
                                    
                                TextEntry::make('uploader.name')
                                    ->label('Uploadé par'),
                                    
                                TextEntry::make('uploaded_at')
                                    ->label('Uploadé le')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ]),
                    
                Section::make('Métadonnées techniques')
                    ->schema([
                        KeyValueEntry::make('technical_info')
                            ->label('Informations techniques')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
