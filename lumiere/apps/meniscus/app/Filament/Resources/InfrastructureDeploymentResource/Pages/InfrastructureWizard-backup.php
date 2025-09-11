<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Pages;

use App\Filament\Resources\InfrastructureDeploymentResource;
use App\Models\InfrastructureDeployment;
use App\Services\TerraformService;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InfrastructureWizard extends CreateRecord
{
    protected static string $resource = InfrastructureDeploymentResource::class;

    protected static ?string $title = 'Infrastructure Wizard';

    protected static ?string $breadcrumb = 'Wizard';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Basic Information')
                        ->description('Give your infrastructure a name and purpose')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Card::make([
                                TextInput::make('name')
                                    ->label('Deployment Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('My DCP Processing Environment')
                                    ->helperText('Choose a descriptive name that will help you identify this deployment'),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->maxLength(65535)
                                    ->rows(3)
                                    ->placeholder('This environment will be used for...')
                                    ->helperText('Optional: Describe what this infrastructure will be used for'),

                                TextInput::make('project_name')
                                    ->label('Project Name')
                                    ->required()
                                    ->default('dcparty')
                                    ->maxLength(255)
                                    ->rules(['regex:/^[a-z0-9-]+$/'])
                                    ->helperText('Used as prefix for all cloud resources (lowercase letters, numbers, and hyphens only)'),
                            ])
                            ->columnSpanFull(),
                        ]),

                    Step::make('Scenario Selection')
                        ->description('Choose the deployment scenario that fits your needs')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Card::make([
                                Select::make('scenario')
                                    ->label('Deployment Scenario')
                                    ->required()
                                    ->options([
                                        InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION => 'Backend Automation',
                                        InfrastructureDeployment::SCENARIO_MANUAL_TESTING => 'Manual Testing',
                                    ])
                                    ->default(InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION)
                                    ->live()
                                    ->helperText('Select the scenario that best matches your use case'),

                                Placeholder::make('scenario_description')
                                    ->content(function ($get) {
                                        return match ($get('scenario')) {
                                            InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION => 
                                                '🏭 **Backend Automation**: Creates a master server that can automatically scale worker instances based on DCP processing demand. Perfect for production environments where you need to process multiple DCPs efficiently.',
                                            InfrastructureDeployment::SCENARIO_MANUAL_TESTING =>
                                                '🧪 **Manual Testing**: Creates a powerful single machine with desktop environment and Guacamole web access. Ideal for manual DCP verification, testing, and quality control by cinema technicians.',
                                            default => 'Please select a scenario to see the description.',
                                        };
                                    }),
                            ])
                            ->columnSpanFull(),

                            Card::make([
                                Select::make('environment')
                                    ->label('Environment Type')
                                    ->required()
                                    ->options([
                                        InfrastructureDeployment::ENV_DEVELOPMENT => 'Development',
                                        InfrastructureDeployment::ENV_STAGING => 'Staging',
                                        InfrastructureDeployment::ENV_PRODUCTION => 'Production',
                                    ])
                                    ->default(InfrastructureDeployment::ENV_DEVELOPMENT)
                                    ->live()
                                    ->helperText('Choose the appropriate environment type'),

                                Placeholder::make('environment_description')
                                    ->content(function ($get) {
                                        return match ($get('environment')) {
                                            InfrastructureDeployment::ENV_DEVELOPMENT => 
                                                '🔧 **Development**: Lower-cost instances suitable for testing and development work.',
                                            InfrastructureDeployment::ENV_STAGING =>
                                                '🚀 **Staging**: Production-like environment for pre-deployment testing.',
                                            InfrastructureDeployment::ENV_PRODUCTION =>
                                                '🏭 **Production**: High-performance instances with full DDoS protection for live workloads.',
                                            default => '',
                                        };
                                    }),
                            ])
                            ->columnSpanFull(),
                        ]),

                    Step::make('Configuration')
                        ->description('Configure provider settings and advanced options')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Card::make([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('provider_config.vultr_region')
                                            ->label('Vultr Region')
                                            ->options([
                                                'fra' => 'Frankfurt (Germany)',
                                                'par' => 'Paris (France)',
                                                'ams' => 'Amsterdam (Netherlands)',
                                                'lon' => 'London (UK)',
                                                'nyc' => 'New York (USA)',
                                                'lax' => 'Los Angeles (USA)',
                                                'sgp' => 'Singapore',
                                                'tok' => 'Tokyo (Japan)',
                                            ])
                                            ->default('fra')
                                            ->helperText('Choose the region closest to your users'),

                                        Select::make('provider_config.master_plan')
                                            ->label('Master Instance Size')
                                            ->options([
                                                'vc2-1c-1gb' => 'Small (1 vCPU, 1GB RAM) - $6/month',
                                                'vc2-2c-4gb' => 'Medium (2 vCPU, 4GB RAM) - $24/month',
                                                'vc2-4c-8gb' => 'Large (4 vCPU, 8GB RAM) - $48/month',
                                                'vc2-8c-16gb' => 'XLarge (8 vCPU, 16GB RAM) - $96/month',
                                            ])
                                            ->default(function ($get) {
                                                return $get('scenario') === InfrastructureDeployment::SCENARIO_MANUAL_TESTING 
                                                    ? 'vc2-4c-8gb' 
                                                    : 'vc2-2c-4gb';
                                            })
                                            ->helperText('Master instance specifications'),
                                    ]),

                                Textarea::make('provider_config.ssh_public_key')
                                    ->label('SSH Public Key')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQ...')
                                    ->helperText('Your SSH public key for accessing the instances'),

                                TextInput::make('provider_config.b2_bucket_name')
                                    ->label('B2 Storage Bucket (Optional)')
                                    ->placeholder('dcparty-storage')
                                    ->helperText('Backblaze B2 bucket for DCP file storage (leave empty if not using)'),
                            ])
                            ->columnSpanFull(),
                        ]),

                    Step::make('Review & Deploy')
                        ->description('Review your configuration and deploy')
                        ->icon('heroicon-o-rocket-launch')
                        ->schema([
                            Card::make([
                                Placeholder::make('review_summary')
                                    ->label('Configuration Summary')
                                    ->content(function ($get) {
                                        $scenario = $get('scenario');
                                        $environment = $get('environment');
                                        $region = $get('provider_config.vultr_region') ?? 'fra';
                                        $masterPlan = $get('provider_config.master_plan') ?? 'vc2-2c-4gb';
                                        
                                        $scenarioLabels = [
                                            InfrastructureDeployment::SCENARIO_BACKEND_AUTOMATION => 'Backend Automation',
                                            InfrastructureDeployment::SCENARIO_MANUAL_TESTING => 'Manual Testing',
                                        ];
                                        
                                        $envLabels = [
                                            InfrastructureDeployment::ENV_DEVELOPMENT => 'Development',
                                            InfrastructureDeployment::ENV_STAGING => 'Staging',
                                            InfrastructureDeployment::ENV_PRODUCTION => 'Production',
                                        ];
                                        
                                        return "**Scenario**: {$scenarioLabels[$scenario]}\n\n" .
                                               "**Environment**: {$envLabels[$environment]}\n\n" .
                                               "**Region**: {$region}\n\n" .
                                               "**Master Instance**: {$masterPlan}\n\n" .
                                               "**Project**: {$get('project_name')}\n\n" .
                                               "Click 'Create' to save this configuration and then deploy from the main interface.";
                                    }),
                            ])
                            ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->submitAction(new Action('create'))
                ->skippable()
                ->persistStepInQueryString(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = InfrastructureDeployment::STATUS_DRAFT;
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Infrastructure Configuration Created')
            ->body('Your infrastructure has been configured successfully. You can now deploy it from the infrastructure list.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to List')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
