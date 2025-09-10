<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Pages;

use App\Filament\Resources\InfrastructureDeploymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfrastructureDeployments extends ListRecords
{
    protected static string $resource = InfrastructureDeploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
// Actions\Action::make('wizard')
            //     ->label('🧙 New Deployment Wizard')
            //     ->icon('heroicon-o-sparkles')
            //     ->color('success')
            //     ->size('lg')
            //     ->url(route('filament.admin.resources.infrastructure-deployments.wizard')),
            
            Actions\CreateAction::make()
                ->label('Quick Create')
                ->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        return 'Infrastructure Deployments';
    }

    public function getHeading(): string
    {
        return 'Infrastructure Deployments';
    }

    public function getSubheading(): string
    {
        return 'Manage your DCP processing infrastructure deployments on Vultr cloud.';
    }
}
