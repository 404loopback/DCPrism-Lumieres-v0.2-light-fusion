<?php

namespace Modules\Fresnel\app\Filament\Infrastructure\Resources\Deployments\Pages;

use Modules\Fresnel\app\Filament\Infrastructure\Resources\Deployments\DeploymentResource;
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
