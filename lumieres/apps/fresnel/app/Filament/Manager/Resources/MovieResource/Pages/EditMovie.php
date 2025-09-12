<?php

namespace App\Filament\Manager\Resources\MovieResource\Pages;

use App\Filament\Manager\Resources\MovieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Session;

class EditMovie extends EditRecord
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    public function getTitle(): string
    {
        $festivalName = Session::get('manager_festival_name');
        
        if ($festivalName) {
            return "Modifier Film - {$festivalName}";
        }
        
        return 'Modifier Film';
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
