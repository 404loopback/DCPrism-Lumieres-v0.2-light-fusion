<?php

namespace Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource\Pages;

use Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInfrastructureDeployment extends ViewRecord
{
    protected static string $resource = InfrastructureDeploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
