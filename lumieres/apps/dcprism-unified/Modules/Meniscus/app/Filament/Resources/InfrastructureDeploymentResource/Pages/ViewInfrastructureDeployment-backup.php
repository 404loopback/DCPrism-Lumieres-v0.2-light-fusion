<?php

namespace Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource\Pages;

use App\Filament\Resources\InfrastructureDeploymentResource;
use App\Services\TerraformService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Section;

class ViewInfrastructureDeployment extends ViewRecord
{
    protected static string $resource = InfrastructureDeploymentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),
                        
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided'),
                            
                        BadgeEntry::make('status')
                            ->label('Status')
                            ->color(fn ($record) => $record->status_color),
                            
                        BadgeEntry::make('scenario_label')
                            ->label('Scenario'),
                            
                        BadgeEntry::make('environment')
                            ->label('Environment')
                            ->color(fn ($record) => $record->environment_color),
                    ])
                    ->columns(2),

                Section::make('Infrastructure Details')
                    ->schema([
                        TextEntry::make('terraform_outputs.master_ips')
                            ->label('Master IPs')
                            ->listWithLineBreaks()
                            ->placeholder('Not deployed yet'),
                            
                        TextEntry::make('terraform_outputs.worker_ips')
                            ->label('Worker IPs')
                            ->listWithLineBreaks()
                            ->placeholder('No workers'),
                            
                        TextEntry::make('estimated_cost')
                            ->label('Estimated Cost')
                            ->money('USD')
                            ->placeholder('Not calculated'),
                            
                        TextEntry::make('deployed_at')
                            ->label('Deployed At')
                            ->dateTime()
                            ->placeholder('Not deployed'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->isDeployed()),

                Section::make('Access Information')
                    ->schema([
                        KeyValueEntry::make('terraform_outputs.access_urls')
                            ->label('Access URLs'),
                    ])
                    ->visible(fn ($record) => $record->isDeployed() && !empty($record->getAccessUrls())),

                Section::make('Configuration')
                    ->schema([
                        KeyValueEntry::make('provider_config')
                            ->label('Provider Configuration'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
