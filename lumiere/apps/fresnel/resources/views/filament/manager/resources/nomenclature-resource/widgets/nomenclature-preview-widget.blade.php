<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🎯 Aperçu Nomenclature
        </x-slot>

        @php
            $data = $previewData;
        @endphp
        
        @if(isset($data['error']) || $data['nomenclatures']->isEmpty())
            <div class="text-center py-4 text-gray-500">Aucune nomenclature disponible</div>
        @else
            {{-- Résultat de la nomenclature --}}
            @php
                $result = $this->generateResult();
                // Si pas de résultat, générer un exemple avec les paramètres actifs
                if (!$result) {
                    $activeParts = [];
                    $separator = '_'; // séparateur par défaut
                    foreach($data['nomenclatures']->where('is_active', true)->sortBy('order_position') as $nom) {
                        $sample = match(strtolower($nom->parameter->name ?? '')) {
                            'titre', 'title' => 'EXEMPLE_FILM',
                            'format', 'resolution' => '4K', 
                            'audio' => '51',
                            'annee', 'year' => '2024',
                            'version' => 'VF',
                            default => strtoupper($nom->parameter->name ?? 'PARAM')
                        };
                        $separator = $nom->separator ?? '_';
                        $activeParts[] = ($nom->prefix ?? '') . $sample . ($nom->suffix ?? '');
                    }
                    $result = implode($separator, $activeParts);
                }
            @endphp
            
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded border border-green-200 dark:border-green-700">
                <div class="text-center mb-2 text-sm text-gray-600 dark:text-gray-400">Nomenclature générée :</div>
                <div class="font-mono text-center text-lg font-bold text-green-800 dark:text-green-200">
                    {{ $result ?: 'Aucun paramètre actif' }}
                </div>
            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
