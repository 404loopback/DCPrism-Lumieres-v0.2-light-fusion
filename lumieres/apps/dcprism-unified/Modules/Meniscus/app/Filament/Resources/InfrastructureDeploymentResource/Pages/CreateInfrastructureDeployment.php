<?php

namespace Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Modules\Meniscus\app\Filament\Resources\InfrastructureDeploymentResource;
use Modules\Meniscus\app\Models\InfrastructureDeployment;

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
