<?php

namespace Modules\Fresnel\app\Filament\Source\Resources\MovieResource\Pages;

use Modules\Fresnel\app\Filament\Source\Resources\MovieResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMovie extends ViewRecord
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_dcps')
                ->label('Gérer les DCPs')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('primary')
                ->url(fn () => route('filament.source.resources.movies.manage-dcps', $this->record))
                ->visible(fn () => !in_array($this->record->status, ['validated', 'rejected'])),
        ];
    }
}
