<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\MovieResource\Pages;

use Modules\Fresnel\app\Filament\Manager\Resources\MovieResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateMovie extends CreateRecord
{
    protected static string $resource = MovieResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function getTitle(): string
    {
        $festivalName = Session::get('manager_festival_name');
        
        if ($festivalName) {
            return "Nouveau Film - {$festivalName}";
        }
        
        return 'Nouveau Film';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return MovieResource::mutateFormDataBeforeCreate($data);
    }
    
    protected function afterCreate(): void
    {
        MovieResource::afterCreate($this->record, $this->data);
    }
}
