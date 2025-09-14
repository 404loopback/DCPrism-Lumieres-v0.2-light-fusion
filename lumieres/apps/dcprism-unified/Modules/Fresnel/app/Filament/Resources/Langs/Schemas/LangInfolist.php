<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Fieldset;

class LangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de base')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('iso_639_1')
                                    ->label('Code ISO 639-1')
                                    ->badge()
                                    ->color('primary')
                                    ->copyable(),
                                    
                                TextEntry::make('iso_639_3')
                                    ->label('Code ISO 639-3')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable(),
                                    
                                TextEntry::make('display_name')
                                    ->label('Affichage')
                                    ->weight('bold')
                                    ->size('lg'),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nom en anglais')
                                    ->copyable(),
                                    
                                TextEntry::make('local_name')
                                    ->label('Nom local')
                                    ->placeholder('Non renseigné')
                                    ->copyable(),
                            ]),
                    ]),
                    
                Section::make('Utilisation')
                    ->schema([
                        Fieldset::make('Versions linguistiques')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('audioVersions')
                                            ->label('Versions avec audio dans cette langue')
                                            ->badge()
                                            ->color('success')
                                            ->formatStateUsing(fn ($state) => $state->count() . ' version(s)')
                                            ->url(fn ($record) => '/admin/versions?filter[audio_lang]=' . $record->iso_639_1, shouldOpenInNewTab: true),
                                            
                                        TextEntry::make('subtitleVersions')
                                            ->label('Versions avec sous-titres dans cette langue')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn ($state) => $state->count() . ' version(s)')
                                            ->url(fn ($record) => '/admin/versions?filter[sub_lang]=' . $record->iso_639_1, shouldOpenInNewTab: true),
                                    ]),
                            ]),
                            
                        Fieldset::make('Fichiers DCP')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('audioDcps')
                                            ->label('DCPs avec audio dans cette langue')
                                            ->badge()
                                            ->color('warning')
                                            ->formatStateUsing(fn ($state) => $state->count() . ' DCP(s)')
                                            ->url(fn ($record) => '/admin/dcps?filter[audio_lang]=' . $record->iso_639_1, shouldOpenInNewTab: true),
                                            
                                        TextEntry::make('subtitleDcps')
                                            ->label('DCPs avec sous-titres dans cette langue')
                                            ->badge()
                                            ->color('gray')
                                            ->formatStateUsing(fn ($state) => $state->count() . ' DCP(s)')
                                            ->url(fn ($record) => '/admin/dcps?filter[subtitle_lang]=' . $record->iso_639_1, shouldOpenInNewTab: true),
                                    ]),
                            ]),
                    ])
                    ->collapsible(),
                    
                Section::make('Métadonnées')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime('d/m/Y H:i:s'),
                                    
                                TextEntry::make('updated_at')
                                    ->label('Modifié le')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
