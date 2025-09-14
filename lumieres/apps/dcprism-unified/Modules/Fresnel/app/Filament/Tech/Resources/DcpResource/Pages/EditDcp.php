<?php

namespace Modules\Fresnel\app\Filament\Tech\Resources\DcpResource\Pages;

use Modules\Fresnel\app\Filament\Tech\Resources\DcpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDcp extends EditRecord
{
    protected static string $resource = DcpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si le statut change vers validé, mettre à jour validated_at
        if (isset($data['is_valid']) && $data['is_valid'] && !$this->record->validated_at) {
            $data['validated_at'] = now();
        }
        
        // Si le statut change vers valide, s'assurer que le statut DCP est cohérent
        if (isset($data['is_valid']) && $data['is_valid'] && $this->record->status !== 'valid') {
            $data['status'] = 'valid';
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // Mettre à jour le statut du film après modification
        DcpResource::updateMovieStatus($this->record->movie);
    }
}
