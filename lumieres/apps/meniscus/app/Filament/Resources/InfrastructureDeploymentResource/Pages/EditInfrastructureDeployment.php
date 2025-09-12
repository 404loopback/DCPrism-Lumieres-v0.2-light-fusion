<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Pages;

use App\Filament\Resources\InfrastructureDeploymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInfrastructureDeployment extends EditRecord
{
    protected static string $resource = InfrastructureDeploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
