<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\Deployments;

use Modules\Fresnel\app\Filament\Infrastructure\Resources\Deployments\Pages\ManageDeployments;
use Modules\Fresnel\app\Models\InfrastructureDeployment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeploymentResource extends Resource
{
    protected static ?string $model = InfrastructureDeployment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;
    
    protected static ?string $navigationLabel = 'Deployments';
    
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Production Environment'),
                
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),
                
                Forms\Components\Select::make('scenario')
                    ->required()
                    ->options([
                        'backend-automation' => 'DCP Processing (Backend)',
                        'manual-testing' => 'Manual Testing Environment',
                        'high-performance-windows' => 'Windows Workstation'
                    ]),
                
                Forms\Components\Select::make('environment')
                    ->required()
                    ->options([
                        'development' => 'Development',
                        'staging' => 'Staging',
                        'production' => 'Production'
                    ])
                    ->default('development'),
                
                Forms\Components\TextInput::make('project_name')
                    ->required()
                    ->default('dcparty'),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'planning' => 'Planning',
                        'deploying' => 'Deploying',
                        'deployed' => 'Deployed',
                        'failed' => 'Failed',
                        'destroying' => 'Destroying',
                        'destroyed' => 'Destroyed',
                    ])
                    ->default('draft')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                \Filament\Tables\Columns\BadgeColumn::make('scenario')
                    ->colors([
                        'success' => 'backend-automation',
                        'info' => 'manual-testing', 
                        'warning' => 'high-performance-windows',
                    ]),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'deploying',
                        'success' => 'deployed',
                        'danger' => 'failed',
                        'secondary' => 'destroyed',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color('primary'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'deploying' => 'Deploying',
                        'deployed' => 'Deployed',
                        'failed' => 'Failed',
                        'destroyed' => 'Destroyed',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('scenario')
                    ->options([
                        'backend-automation' => 'Backend Automation',
                        'manual-testing' => 'Manual Testing',
                        'high-performance-windows' => 'Windows Workstation',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('deploy')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (InfrastructureDeployment $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(fn (InfrastructureDeployment $record) => $record->update(['status' => 'deploying'])),
                \Filament\Actions\Action::make('destroy')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (InfrastructureDeployment $record) => in_array($record->status, ['deployed', 'failed']))
                    ->requiresConfirmation()
                    ->action(fn (InfrastructureDeployment $record) => $record->update(['status' => 'destroyed'])),
                DeleteAction::make()
                    ->visible(fn (InfrastructureDeployment $record) => in_array($record->status, ['destroyed', 'draft'])),
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
            'index' => ManageDeployments::route('/'),
        ];
    }
}
