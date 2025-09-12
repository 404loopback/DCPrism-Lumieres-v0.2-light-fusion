<?php

namespace App\Filament\Resources\Versions\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class VersionInfolist
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
                                    
                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->color('primary'),
                                    
                                TextEntry::make('generated_nomenclature')
                                    ->label('Nomenclature')
                                    ->copyable(),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('audioLanguage.display_name')
                                    ->label('Langue audio'),
                                    
                                TextEntry::make('subtitleLanguage.display_name')
                                    ->label('Langue sous-titres')
                                    ->placeholder('Aucune'),
                            ]),
                    ]),
                    
                Section::make('DCPs associés')
                    ->schema([
                        TextEntry::make('dcps')
                            ->label('Liste des DCPs')
                            ->formatStateUsing(fn ($state) => $state->count() . ' DCP(s) associé(s)')
                            ->badge()
                            ->color('warning'),
                    ])
                    ->collapsible(),
            ]);
    }
}
