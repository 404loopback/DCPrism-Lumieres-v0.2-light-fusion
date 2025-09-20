<?php

namespace Modules\Fresnel\app\Filament\Resources\Langs\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de la langue')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('iso_639_1')
                                    ->label('Code ISO 639-1')
                                    ->placeholder('fr, en, de, es...')
                                    ->length(2)
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Code langue sur 2 lettres (ISO 639-1)'),

                                TextInput::make('iso_639_3')
                                    ->label('Code ISO 639-3')
                                    ->placeholder('fra, eng, deu, spa...')
                                    ->length(3)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Code langue sur 3 lettres (ISO 639-3)'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom en anglais')
                                    ->placeholder('French, English, German, Spanish...')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('local_name')
                                    ->label('Nom local')
                                    ->placeholder('Français, English, Deutsch, Español...')
                                    ->maxLength(100)
                                    ->helperText('Nom de la langue dans sa propre langue'),
                            ]),
                    ]),

                Section::make('Aperçu')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('display_preview')
                            ->label('Affichage')
                            ->content(function ($get) {
                                $name = $get('name');
                                $localName = $get('local_name');
                                $iso1 = $get('iso_639_1');

                                if (! $name && ! $localName) {
                                    return 'Remplissez les champs ci-dessus pour voir l\'aperçu';
                                }

                                $display = $name;
                                if ($localName && $localName !== $name) {
                                    $display .= ' ('.$localName.')';
                                }
                                if ($iso1) {
                                    $display .= ' ['.$iso1.']';
                                }

                                return $display;
                            })
                            ->live(onBlur: true),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
