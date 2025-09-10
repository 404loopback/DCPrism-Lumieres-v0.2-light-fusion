<?php

namespace App\Filament\Tech\Resources\DcpResource\Pages;

use App\Filament\Tech\Resources\DcpResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;

class ViewDcp extends ViewRecord
{
    protected static string $resource = DcpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('validate')
                ->label('Valider ce DCP')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Valider le DCP')
                ->modalDescription('Confirmer que ce DCP est techniquement valide ?')
                ->action(function () {
                    $this->record->markAsValid('DCP validé par technicien le ' . now()->format('d/m/Y H:i'));
                    DcpResource::updateMovieStatus($this->record->movie);
                    
                    $this->notify('success', 'DCP validé avec succès');
                })
                ->visible(fn () => !$this->record->is_valid),

            Actions\Action::make('reject')
                ->label('Rejeter ce DCP')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Motif de Rejet')
                        ->required()
                        ->placeholder('Expliquez pourquoi ce DCP est rejeté...')
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->markAsInvalid($data['rejection_reason']);
                    
                    $this->notify('warning', 'DCP rejeté');
                })
                ->visible(fn () => $this->record->status !== 'invalid'),

            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin', 'tech'])),

            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin'])),
        ];
    }
}
