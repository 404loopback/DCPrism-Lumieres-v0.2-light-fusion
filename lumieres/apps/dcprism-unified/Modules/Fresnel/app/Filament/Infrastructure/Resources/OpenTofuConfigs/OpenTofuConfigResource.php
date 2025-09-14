<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\OpenTofuConfigs;

use Modules\Fresnel\app\Filament\Infrastructure\Resources\OpenTofuConfigs\Pages\ManageOpenTofuConfigs;
use Modules\Fresnel\app\Models\OpenTofuConfig;
use Modules\Fresnel\app\Services\OpenTofuManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OpenTofuConfigResource extends Resource
{
    protected static ?string $model = OpenTofuConfig::class;

    // protected static ?string $navigationIcon = 'heroicon-o-code-bracket';
    
    protected static ?string $navigationLabel = 'OpenTofu Configs';
    
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        $openTofuManager = app(OpenTofuManager::class);
        $providers = $openTofuManager->getAvailableProviders();
        $scenarios = $openTofuManager->getAvailableScenarios();
        
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Production DCP Environment'),
                
                Forms\Components\Select::make('scenario')
                    ->label('Deployment Scenario')
                    ->required()
                    ->options(collect($scenarios)->mapWithKeys(fn($config, $key) => [$key => $config['name']]))
                    ->reactive(),
                    
                Forms\Components\Select::make('provider')
                    ->label('Cloud Provider')
                    ->required()
                    ->options(collect($providers)->mapWithKeys(fn($config, $key) => [$key => $config['name']]))
                    ->reactive(),
                
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),
                
                Forms\Components\TextInput::make('region')
                    ->label('Region')
                    ->placeholder('e.g., fra, us-east-1'),
                
                Forms\Components\TextInput::make('instance_count')
                    ->label('Instance Count')
                    ->numeric()
                    ->default(1),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'created' => 'Created',
                        'planned' => 'Planned',
                        'deployed' => 'Deployed',
                        'failed' => 'Failed',
                        'destroyed' => 'Destroyed'
                    ])
                    ->default('created')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                
                Tables\Columns\TextColumn::make('scenario')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'backend-automation' => 'success',
                        'manual-testing' => 'info',
                        'high-performance-windows' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'backend-automation' => 'Backend Automation',
                        'manual-testing' => 'Manual Testing',
                        'high-performance-windows' => 'Windows Workstation',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vultr' => 'primary',
                        'aws' => 'warning',
                        'azure' => 'info',
                        'gcp' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vultr' => 'Vultr',
                        'aws' => 'AWS',
                        'azure' => 'Azure',
                        'gcp' => 'GCP',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'gray',
                        'planning' => 'warning',
                        'planned' => 'info',
                        'applying' => 'primary',
                        'deployed' => 'success',
                        'failed' => 'danger',
                        'destroyed' => 'secondary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scenario')
                    ->options([
                        'backend-automation' => 'Backend Automation',
                        'manual-testing' => 'Manual Testing',
                        'high-performance-windows' => 'Windows Workstation',
                    ]),
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'vultr' => 'Vultr',
                        'aws' => 'AWS',
                        'azure' => 'Azure',
                        'gcp' => 'GCP',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('plan')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->action(function (OpenTofuConfig $record) {
                        try {
                            $record->generateFiles();
                            $openTofuManager = $record->getOpenTofuManager();
                            $result = $openTofuManager->planConfiguration($record->name);
                            $record->update(['status' => 'planned']);
                            Notification::make()
                                ->title('Plan Generated')
                                ->body('OpenTofu plan generated successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            $record->update(['status' => 'failed']);
                            Notification::make()
                                ->title('Plan Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (OpenTofuConfig $record) => in_array($record->status, ['created', 'failed'])),
                
                \Filament\Actions\Action::make('apply')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->action(function (OpenTofuConfig $record) {
                        try {
                            $result = $record->deploy();
                            if ($result) {
                                Notification::make()
                                    ->title('Deployment Started')
                                    ->body('Infrastructure deployment initiated')
                                    ->success()
                                    ->send();
                            } else {
                                throw new \Exception('Deployment failed');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Deployment Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Deploy Infrastructure')
                    ->modalDescription('This will deploy the infrastructure. Make sure you have reviewed the plan.')
                    ->visible(fn (OpenTofuConfig $record) => in_array($record->status, ['created', 'planned', 'failed'])),
                
                \Filament\Actions\Action::make('destroy')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function (OpenTofuConfig $record) {
                        try {
                            $result = $record->destroyInfrastructure();
                            if ($result) {
                                Notification::make()
                                    ->title('Destroy Initiated')
                                    ->body('Infrastructure destruction started')
                                    ->success()
                                    ->send();
                            } else {
                                throw new \Exception('Destroy failed');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Destroy Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Destroy Infrastructure')
                    ->modalDescription('⚠️ This will permanently destroy all resources. This cannot be undone!')
                    ->visible(fn (OpenTofuConfig $record) => $record->status === 'deployed'),
                
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOpenTofuConfigs::route('/'),
        ];
    }
    
}
