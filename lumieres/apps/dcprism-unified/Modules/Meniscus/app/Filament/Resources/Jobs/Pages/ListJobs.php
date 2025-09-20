<?php

namespace Modules\Meniscus\app\Filament\Resources\Jobs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Meniscus\app\Filament\Resources\Jobs\JobResource;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
