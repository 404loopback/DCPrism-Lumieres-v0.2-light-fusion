<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Schemas;

use App\Models\InfrastructureDeployment;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class InfrastructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('My DCP Processing Environment'),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->placeholder('Environment for DCP encoding and validation...'),

                        TextInput::make('project_name')
                            ->required()
                            ->default('dcparty')
                            ->maxLength(255),
                    ]),

                Section::make('Configuration')
                    ->components([
                        Select::make('scenario')
                            ->required()
                            ->options([
                                InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION => 'Backend Automation',
                                InfrastructureDeployment::SCENARIO_MANUAL_TESTING => 'Manual Testing',
                            ])
                            ->default(InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION),

                        Select::make('environment')
                            ->required()
                            ->options([
                                InfrastructureDeployment::ENV_DEVELOPMENT => 'Development',
                                InfrastructureDeployment::ENV_STAGING => 'Staging',
                                InfrastructureDeployment::ENV_PRODUCTION => 'Production',
                            ])
                            ->default(InfrastructureDeployment::ENV_DEVELOPMENT),
                    ]),
            ]);
    }
}
