<?php

namespace Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource;

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
