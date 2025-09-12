<?php

namespace App\Filament\Resources\InfrastructureDeploymentResource\Pages;

use App\Filament\Resources\InfrastructureDeploymentResource;
use App\Models\InfrastructureDeployment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInfrastructureDeployment extends CreateRecord
{
    protected static string $resource = InfrastructureDeploymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = InfrastructureDeployment::STATUS_DRAFT;
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
