<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\Deployments\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Modules\Fresnel\app\Filament\Infrastructure\Resources\Deployments\DeploymentResource;

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
