<?php

namespace Modules\Fresnel\app\Filament\Manager\Resources\NomenclatureResource\Widgets;

use Modules\Fresnel\app\Models\Festival;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

class NomenclaturePreviewWidget extends Widget
{
    protected string $view = 'filament.manager.resources.nomenclature-resource.widgets.nomenclature-preview-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 10;
    
    // Propriétés du widget
    public ?int $festivalId = null;
    
    public bool $isLoading = false;
    public array $draggedOrder = [];
    public string $lastGeneratedResult = '';
    
    public function mount(): void
    {
        $this->festivalId = Session::get('selected_festival_id');
        $this->initializeDragOrder();
    }
    
    protected function getViewData(): array
    {
        return [
            'widget' => $this,
            'festivalId' => $this->festivalId,
            'previewData' => $this->getNomenclaturePreviewData(),
            'draggedOrder' => $this->draggedOrder,
            'isLoading' => $this->isLoading,
            'lastGeneratedResult' => $this->lastGeneratedResult,
        ];
    }
    
    public function getHeading(): ?string
    {
        return '🎯 Générateur de Nomenclature Dynamique';
    }
    
    public function getDescription(): ?string
    {
        return 'Organisez vos paramètres par glisser-déposer pour générer automatiquement votre nomenclature.';
    }
    
    public function getNomenclaturePreviewData(): array
    {
        if (!$this->festivalId) {
            return [
                'error' => true,
                'message' => 'Aucun festival sélectionné.',
                'icon' => '⚠️'
            ];
        }
        
        return Cache::remember(
            "nomenclature_preview_{$this->festivalId}",
            now()->addMinutes(10),
            fn() => $this->fetchNomenclatureData()
        );
    }
    
    protected function fetchNomenclatureData(): array
    {
        try {
            $festival = Festival::find($this->festivalId);
            
            if (!$festival) {
                return [
                    'error' => true,
                    'message' => 'Festival introuvable.',
                    'icon' => '❌'
                ];
            }
            
            $nomenclatures = $festival->nomenclatures()
                ->with('parameter')
                ->orderBy('order_position')
                ->get();
            
            return [
                'festival' => $festival,
                'nomenclatures' => $nomenclatures,
                'success' => true,
                'stats' => $this->calculateStats($nomenclatures),
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Erreur: ' . $e->getMessage(),
                'icon' => '🚫'
            ];
        }
    }
    
    protected function calculateStats($nomenclatures): array
    {
        return [
            'total' => $nomenclatures->count(),
            'active' => $nomenclatures->where('is_active', true)->count(),
            'required' => $nomenclatures->where('is_required', true)->count(),
            'inactive' => $nomenclatures->where('is_active', false)->count(),
        ];
    }
    
    protected function initializeDragOrder(): void
    {
        $data = $this->getNomenclaturePreviewData();
        if (!isset($data['error'])) {
            $this->draggedOrder = $data['nomenclatures']->pluck('id')->toArray();
        }
    }
    
    #[On('nomenclature-order-changed')]
    public function updateOrder(array $orderedIds): void
    {
        $this->draggedOrder = $orderedIds;
        $this->generateResult();
        $this->dispatch('order-updated', orderedIds: $orderedIds);
        
        // Invalider le cache
        Cache::forget("nomenclature_preview_{$this->festivalId}");
    }
    
    #[On('nomenclature-toggle')]
    public function toggleNomenclature(int $nomenclatureId, bool $isActive): void
    {
        // Logique pour basculer l'état actif/inactif
        $this->dispatch('nomenclature-toggled', id: $nomenclatureId, active: $isActive);
        $this->generateResult();
    }
    
    public function generateResult(): string
    {
        $data = $this->getNomenclaturePreviewData();
        
        if (isset($data['error'])) {
            return '';
        }
        
        $nomenclatures = $data['nomenclatures'];
        $parts = [];
        $separator = '_'; // séparateur par défaut
        
        // Utiliser les nomenclatures triées par order_position si draggedOrder est vide
        $orderedIds = !empty($this->draggedOrder) ? $this->draggedOrder : $nomenclatures->pluck('id')->toArray();
        
        foreach ($orderedIds as $id) {
            $nomenclature = $nomenclatures->firstWhere('id', $id);
            
            if (!$nomenclature || !$nomenclature->is_active) {
                continue;
            }
            
            // Utiliser le séparateur du premier paramètre actif
            if (empty($parts)) {
                $separator = $nomenclature->separator ?? '_';
            }
            
            // Utiliser la méthode getPreview() de la nomenclature qui utilise les vraies valeurs
            $formatted = $nomenclature->getPreview($nomenclature->default_value);
            
            if ($formatted) {
                $parts[] = $formatted;
            }
        }
        
        $this->lastGeneratedResult = implode($separator, $parts);
        
        return $this->lastGeneratedResult;
    }
    
    public function refreshData(): void
    {
        $this->isLoading = true;
        
        // Invalider le cache
        Cache::forget("nomenclature_preview_{$this->festivalId}");
        
        // Réinitialiser les données
        $this->initializeDragOrder();
        
        $this->isLoading = false;
        
        $this->dispatch('data-refreshed');
    }
    
    public function exportConfiguration(): array
    {
        $data = $this->getNomenclaturePreviewData();
        
        if (isset($data['error'])) {
            return [];
        }
        
        return [
            'festival' => $data['festival']->name,
            'nomenclatures' => $data['nomenclatures']->map(fn($n) => [
                'parameter' => $n->parameter?->name,
                'order' => $n->order_position,
                'active' => $n->is_active,
                'required' => $n->is_required,
                'prefix' => $n->prefix,
                'suffix' => $n->suffix,
                'separator' => $n->separator,
            ]),
            'generated_at' => now()->toISOString(),
        ];
    }
}
