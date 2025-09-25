<?php

namespace Modules\Meniscus\app\Filament\Resources\Jobs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Meniscus\app\Filament\Resources\Jobs\JobResource;

class CreateJob extends CreateRecord
{
    protected static string $resource = JobResource::class;
}
