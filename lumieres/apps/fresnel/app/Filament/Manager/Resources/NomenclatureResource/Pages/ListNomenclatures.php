<?php

namespace App\Filament\Manager\Resources\NomenclatureResource\Pages;

use App\Filament\Manager\Resources\NomenclatureResource;
use App\Filament\Manager\Resources\NomenclatureResource\Widgets\NomenclaturePreviewWidget;
use App\Services\UnifiedNomenclatureService;
use App\Models\Festival;
use App\Models\Nomenclature;
use App\Traits\SafeTableReordering;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;

class ListNomenclatures extends ListRecords
{
    use SafeTableReordering;
    
    protected static string $resource = NomenclatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            NomenclaturePreviewWidget::class,
        ];
    }
    
    /**
     * Obtenir les données pour l'aperçu de nomenclature
     */
    public function getNomenclaturePreviewData()
    {
        $festivalId = Session::get('selected_festival_id');
        
        if (!$festivalId) {
            return [
                'error' => true,
                'message' => 'Aucun festival sélectionné. Veuillez d\'abord choisir un festival à administrer.',
                'icon' => '⚠️'
            ];
        }
        
        $festival = Festival::find($festivalId);
        if (!$festival) {
            return [
                'error' => true,
                'message' => 'Festival introuvable. Le festival sélectionné n\'existe plus.',
                'icon' => '❌'
            ];
        }
        
        try {
            $nomenclatures = $festival->nomenclatures()->with('parameter')->orderBy('order_position')->get();
            
            return [
                'festival' => $festival,
                'nomenclatures' => $nomenclatures,
                'success' => true
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Erreur lors de la génération de l\'aperçu: ' . $e->getMessage(),
                'icon' => '🚫'
            ];
        }
    }
    
    /**
     * Génère des valeurs d'exemple pour l'aperçu
     */
    private function getExampleValue(string $parameterCode): string
    {
        return match($parameterCode) {
            'TITLE' => 'ExampleMovie',
            'YEAR' => '2024',
            'DURATION' => '120',
            'FORMAT' => '2K',
            'GENRE' => 'Drama',
            'COUNTRY' => 'FR',
            'DIRECTOR' => 'Director',
            'LANG' => 'FR',
            'SUBTITLE' => 'EN',
            default => 'Example'
        };
    }
    
}
