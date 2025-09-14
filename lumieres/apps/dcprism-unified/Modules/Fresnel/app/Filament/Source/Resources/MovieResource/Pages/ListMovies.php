<?php

namespace Modules\Fresnel\app\Filament\Source\Resources\MovieResource\Pages;

use Modules\Fresnel\app\Filament\Source\Resources\MovieResource;
use Filament\Resources\Pages\ListRecords;

class ListMovies extends ListRecords
{
    protected static string $resource = MovieResource::class;

    public function getTitle(): string
    {
        return 'Mes Films à Traiter';
    }
    
    public function getSubheading(): ?string
    {
        $totalMovies = $this->getTableQuery()->count();
        
        if ($totalMovies === 0) {
            return 'Aucun film assigné pour le moment';
        }
        
        $pendingMovies = $this->getTableQuery()
            ->whereIn('status', ['pending', 'token_sent'])
            ->count();
            
        if ($pendingMovies > 0) {
            return "Vous avez {$pendingMovies} film(s) en attente d'upload sur {$totalMovies} total";
        }
        
        return "Tous vos {$totalMovies} films sont traités";
    }
}
