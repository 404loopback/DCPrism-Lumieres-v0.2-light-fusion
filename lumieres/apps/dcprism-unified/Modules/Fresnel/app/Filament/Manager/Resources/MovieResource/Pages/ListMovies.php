<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\MovieResource\Pages;

use Modules\Fresnel\app\Filament\Manager\Resources\MovieResource;
use Modules\Fresnel\app\Filament\Manager\Widgets\MoviesCardsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use Modules\Fresnel\app\Models\Movie;
use Filament\Notifications\Notification;

class ListMovies extends ListRecords
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        $festivalId = Session::get('selected_festival_id');
        $festivalName = Session::get('selected_festival_name');
        
        if (!$festivalId) {
            return [
                Actions\Action::make('select_festival')
                    ->label('Sélectionner un Festival')
                    ->icon('heroicon-o-building-office')
                    ->color('warning')
                    ->url('/panel/manager'),
            ];
        }
        
        return [
            Actions\CreateAction::make()
                ->label('Nouveau Film')
                ->createAnother(false),
                
            Actions\Action::make('festival_info')
                ->label("Festival: {$festivalName}")
                ->icon('heroicon-o-information-circle')
                ->color('gray')
                ->url('/panel/manager')
                ->outlined(),
        ];
    }
    
    public function getTitle(): string
    {
        $festivalName = Session::get('selected_festival_name');
        
        if ($festivalName) {
            return "Films - {$festivalName}";
        }
        
        return 'Films';
    }
    
    public function getSubheading(): ?string
    {
        $festivalId = Session::get('selected_festival_id');
        
        if (!$festivalId) {
            return '⚠️ Veuillez d\'abord sélectionner un festival à administrer';
        }
        
        return 'Gestion des films pour ce festival';
    }
    
}
