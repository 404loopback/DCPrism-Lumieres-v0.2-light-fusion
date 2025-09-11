<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Pages;

use App\Filament\Resources\InfrastructureDeploymentResource;
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
