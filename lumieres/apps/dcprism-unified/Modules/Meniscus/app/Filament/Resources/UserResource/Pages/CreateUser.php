<?php

namespace Modules\Meniscus\app\Filament\Resources\UserResource\Pages;

use Modules\Meniscus\app\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
