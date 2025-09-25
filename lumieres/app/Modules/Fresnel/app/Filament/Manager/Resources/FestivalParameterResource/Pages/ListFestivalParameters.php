<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\FestivalParameterResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Modules\Fresnel\app\Filament\Manager\Pages\SelectParameters;
use Modules\Fresnel\app\Filament\Manager\Resources\FestivalParameterResource;

class ListFestivalParameters extends ListRecords
{
    protected static string $resource = FestivalParameterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('select_parameters')
                ->label('Ajouter des Paramètres')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(SelectParameters::getUrl())
                ->tooltip('Sélectionner des paramètres globaux à ajouter au festival'),
        ];
    }

    public function getTitle(): string
    {
        return 'Paramètres du Festival';
    }

    public function getSubheading(): ?string
    {
        return 'Sélectionnez et personnalisez les paramètres globaux pour votre festival. Tous les paramètres sont créés par les administrateurs - vous ne pouvez que les ajouter et les personnaliser.';
    }
}
