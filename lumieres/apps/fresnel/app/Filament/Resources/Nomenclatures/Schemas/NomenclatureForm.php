<?php

namespace App\Filament\Resources\Nomenclatures\Schemas;

use App\Models\Festival;
use App\Models\Parameter;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class NomenclatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Association')
                    ->schema([
                        Forms\Components\Select::make('festival_id')
                            ->label('Festival')
                            ->options(Festival::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('parameter_id')
                            ->label('Paramètre')
                            ->options(Parameter::where('is_active', true)
                                ->orderBy('category')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($param) => [
                                    $param->id => "{$param->category} - {$param->name}"
                                ]))
                            ->required()
                            ->searchable(),
                    ])->columns(2),
                    
                Section::make('Configuration de position')
                    ->schema([
                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans la nomenclature')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Ordre d\'apparition dans la nomenclature générée'),
                        Forms\Components\TextInput::make('separator')
                            ->label('Séparateur')
                            ->default('_')
                            ->maxLength(10)
                            ->helperText('Caractère de séparation (ex: _, -, .)'),
                    ])->columns(2),
                    
                Section::make('Formatage avancé')
                    ->schema([
                        Forms\Components\TextInput::make('prefix')
                            ->label('Préfixe')
                            ->maxLength(50)
                            ->helperText('Texte ajouté avant la valeur du paramètre'),
                        Forms\Components\TextInput::make('suffix')
                            ->label('Suffixe')
                            ->maxLength(50)
                            ->helperText('Texte ajouté après la valeur du paramètre'),
                        Forms\Components\TextInput::make('default_value')
                            ->label('Valeur par défaut')
                            ->helperText('Utilisée si le paramètre n\'a pas de valeur'),
                    ])->columns(2),
                    
                Section::make('Règles complexes')
                    ->schema([
                        Forms\Components\KeyValue::make('formatting_rules')
                            ->label('Règles de formatage')
                            ->helperText('JSON avec des règles de transformation (uppercase, lowercase, etc.)'),
                        Forms\Components\KeyValue::make('conditional_rules')
                            ->label('Règles conditionnelles')
                            ->helperText('JSON avec des conditions d\'application'),
                    ]),
                    
                Section::make('État')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->helperText('Détermine si ce paramètre est utilisé dans la nomenclature'),
                        Forms\Components\Toggle::make('is_required')
                            ->label('Obligatoire')
                            ->helperText('Empêche la validation si ce paramètre est vide'),
                    ])->columns(2),
            ]);
    }
}
