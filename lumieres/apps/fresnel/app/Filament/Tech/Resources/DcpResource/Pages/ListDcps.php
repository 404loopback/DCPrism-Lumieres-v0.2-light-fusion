<?php

namespace App\Filament\Tech\Resources\DcpResource\Pages;

use App\Filament\Tech\Resources\DcpResource;
use App\Models\Dcp;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListDcps extends ListRecords
{
    protected static string $resource = DcpResource::class;

    public function getTitle(): string
    {
        return 'Validation des DCPs';
    }
    
    public function getSubheading(): ?string
    {
        $pendingCount = $this->getTableQuery()->where('status', Dcp::STATUS_UPLOADED)->count();
        $totalCount = $this->getTableQuery()->count();
        
        if ($pendingCount > 0) {
            return "⏳ {$pendingCount} DCPs en attente de validation sur {$totalCount} total";
        }
        
        return "✅ Tous les DCPs traités ({$totalCount} total)";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_validate_all')
                ->label('Valider Tous les DCPs en Attente')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Validation en masse')
                ->modalDescription('Êtes-vous sûr de vouloir valider TOUS les DCPs en attente ? Cette action est irréversible.')
                ->action(function () {
                    $pendingDcps = $this->getTableQuery()
                        ->where('status', Dcp::STATUS_UPLOADED)
                        ->where('is_valid', false)
                        ->get();
                    
                    $count = 0;
                    foreach ($pendingDcps as $dcp) {
                        $dcp->markAsValid('DCP validé en masse par technicien le ' . now()->format('d/m/Y H:i'));
                        DcpResource::updateMovieStatus($dcp->movie);
                        $count++;
                    }
                    
                    $this->notify('success', "Validation terminée : {$count} DCPs validés");
                })
                ->visible(function () {
                    return $this->getTableQuery()->where('status', Dcp::STATUS_UPLOADED)->count() > 0;
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous les DCPs')
                ->badge(fn () => $this->getTableQuery()->count()),
                
            'pending' => Tab::make('En Attente')
                ->badge(fn () => $this->getTableQuery()->where('status', Dcp::STATUS_UPLOADED)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn ($query) => $query->where('status', Dcp::STATUS_UPLOADED)),
                
            'processing' => Tab::make('En Traitement')
                ->badge(fn () => $this->getTableQuery()->where('status', Dcp::STATUS_PROCESSING)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->where('status', Dcp::STATUS_PROCESSING)),
                
            'validated' => Tab::make('Validés')
                ->badge(fn () => $this->getTableQuery()->where('is_valid', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('is_valid', true)),
                
            'rejected' => Tab::make('Rejetés')
                ->badge(fn () => $this->getTableQuery()->where('is_valid', false)->where('status', Dcp::STATUS_INVALID)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('is_valid', false)->where('status', Dcp::STATUS_INVALID)),
        ];
    }
}
