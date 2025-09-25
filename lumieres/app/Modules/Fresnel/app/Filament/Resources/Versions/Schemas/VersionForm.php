<?php

namespace Modules\Fresnel\app\Filament\Resources\Versions\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Fresnel\app\Models\Lang;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Version;

class VersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Film et type de version')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('movie_id')
                                    ->label('Film')
                                    ->relationship('movie', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Auto-generate nomenclature when movie changes
                                        $set('generated_nomenclature', null);
                                    }),

                                Select::make('type')
                                    ->label('Type de version')
                                    ->options(Version::TYPES)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Auto-generate nomenclature when type changes
                                        $set('generated_nomenclature', null);
                                    })
                                    ->helperText('VO = Version Originale, VOST = VO Sous-titrée, etc.'),
                            ]),
                    ]),

                Section::make('Configuration linguistique')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('audio_lang')
                                    ->label('Langue audio')
                                    ->relationship('audioLanguage', 'name',
                                        fn ($query) => $query->orderBy('name')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable(['name', 'local_name', 'iso_639_1'])
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('generated_nomenclature', null);
                                    }),

                                Select::make('sub_lang')
                                    ->label('Langue sous-titres')
                                    ->relationship('subtitleLanguage', 'name',
                                        fn ($query) => $query->orderBy('name')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable(['name', 'local_name', 'iso_639_1'])
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('generated_nomenclature', null);
                                    }),
                            ]),
                    ]),

                Section::make('Relations et hiérarchie')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('ov_id')
                                    ->label('Version originale de référence')
                                    ->relationship('originalVersion', 'type')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->movie->title.' - '.$record->type.' ('.
                                        $record->audio_lang.')'
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Uniquement pour les versions doublées/sous-titrées'),

                                TagsInput::make('vf_ids')
                                    ->label('IDs des versions françaises liées')
                                    ->placeholder('ID1, ID2, ID3...')
                                    ->helperText('IDs des versions françaises dérivées'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Accessibilité')
                    ->schema([
                        Select::make('accessibility')
                            ->label('Options d\'accessibilité')
                            ->options([
                                'HI' => 'Hard of Hearing (Malentendants)',
                                'VI' => 'Visually Impaired (Malvoyants)',
                                'AD' => 'Audio Description',
                                'CC' => 'Closed Captions',
                            ])
                            ->multiple()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('generated_nomenclature', null);
                            }),
                    ])
                    ->collapsible(),

                Section::make('Nomenclature automatique')
                    ->schema([
                        Placeholder::make('nomenclature_preview')
                            ->label('Aperçu de la nomenclature')
                            ->content(function ($get) {
                                $movieId = $get('movie_id');
                                $type = $get('type');
                                $audioLang = $get('audio_lang');
                                $subLang = $get('sub_lang');
                                $accessibility = $get('accessibility');

                                if (! $movieId || ! $type) {
                                    return 'Remplissez le film et le type pour voir la nomenclature';
                                }

                                $movie = Movie::find($movieId);
                                $audioLangModel = $audioLang ? Lang::where('iso_639_1', $audioLang)->first() : null;
                                $subLangModel = $subLang ? Lang::where('iso_639_1', $subLang)->first() : null;

                                if (! $movie) {
                                    return 'Film non trouvé';
                                }

                                $parts = [$movie->title, $type];

                                if ($audioLangModel) {
                                    $parts[] = $audioLangModel->iso_639_1;
                                }

                                if ($subLangModel) {
                                    $parts[] = 'ST'.$subLangModel->iso_639_1;
                                }

                                if ($accessibility) {
                                    $parts[] = implode('_', (array) $accessibility);
                                }

                                return implode('_', $parts);
                            })
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('generated_nomenclature')
                            ->label('Nomenclature générée')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Cette nomenclature sera générée automatiquement à la sauvegarde')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
