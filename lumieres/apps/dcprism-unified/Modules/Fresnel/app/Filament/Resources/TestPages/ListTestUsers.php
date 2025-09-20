<?php

namespace Modules\Fresnel\app\Filament\Resources\TestPages;

use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Resources\TestResource;

class ListTestUsers extends ListRecords
{
    protected static string $resource = TestResource::class;
}
