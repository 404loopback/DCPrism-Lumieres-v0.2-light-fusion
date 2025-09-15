<?php

namespace Modules\Meniscus\app\Filament\Resources\Deployments\Pages;

use Modules\Meniscus\app\Filament\Resources\Deployments\DeploymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDeployments extends ManageRecords
{
    protected static string $resource = DeploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
