<?php

namespace App\Filament\Tech\Resources\MovieResource\Pages;

use App\Filament\Tech\Resources\MovieResource;
use App\Models\Dcp;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewMovie extends ViewRecord
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('validate_all_dcps')
                ->label('Valider Tous les DCPs')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Valider tous les DCPs du film')
                ->modalDescription(function () {
                    $pendingCount = $this->record->dcps()->where('status', Dcp::STATUS_UPLOADED)->count();
                    return "Valider les {$pendingCount} DCPs en attente pour ce film ?";
                })
                ->action(function () {
                    $pendingDcps = $this->record->dcps()
                        ->where('status', Dcp::STATUS_UPLOADED)
                        ->where('is_valid', false)
                        ->get();
                    
                    $count = 0;
                    foreach ($pendingDcps as $dcp) {
                        $dcp->markAsValid('DCP validé par technicien le ' . now()->format('d/m/Y H:i'));
                        $count++;
                    }
                    
                    // Mettre à jour le statut global du film
                    if ($count > 0) {
                        $this->record->update([
                            'status' => 'validated',
                            'validated_at' => now(),
                            'validated_by' => auth()->id(),
                        ]);
                    }
                    
                    Notification::make()
                        ->title('Validation terminée')
                        ->body("{$count} DCPs validés pour le film {$this->record->title}")
                        ->success()
                        ->send();
                })
                ->visible(function () {
                    return $this->record->dcps()->where('status', Dcp::STATUS_UPLOADED)->count() > 0;
                }),

            Actions\Action::make('view_dcps')
                ->label('Voir DCPs de ce Film')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(function () {
                    return route('filament.tech.resources.dcps.index', [
                        'tableFilters' => [
                            'movie' => ['value' => $this->record->id]
                        ]
                    ]);
                }),
        ];
    }
}
