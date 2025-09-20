<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🎯 Aperçu Nomenclature
        </x-slot>

        @php
            $data = $previewData;
        @endphp
        
        @if(isset($data['error']) || $data['nomenclatures']->isEmpty())
            <div class="text-center py-8">
                <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                <div class="text-gray-500">Aucune nomenclature configurée</div>
                <p class="text-xs text-gray-400 mt-1">Ajoutez des paramètres pour commencer</p>
            </div>
        @else
            {{-- Résultat de la nomenclature --}}
            @php
                $result = $this->generateResult();
                // Si pas de résultat, générer un exemple avec les paramètres actifs
                if (!$result) {
                    $activeParts = [];
                    $separator = '_'; // séparateur par défaut
                    foreach($data['nomenclatures']->where('is_active', true)->sortBy('order_position') as $nom) {
                        $param = $nom->resolveParameter();
                        $sample = match(strtolower($param->name ?? '')) {
                            'titre', 'title' => 'EXEMPLE_FILM',
                            'format', 'resolution' => '4K', 
                            'audio' => '51',
                            'annee', 'year' => '2024',
                            'version' => 'VF',
                            default => strtoupper($param->name ?? 'PARAM')
                        };
                        $separator = $nom->separator ?? '_';
                        $activeParts[] = ($nom->prefix ?? '') . $sample . ($nom->suffix ?? '');
                    }
                    $result = implode($separator, $activeParts);
                }
            @endphp
            
            {{-- Résultat de la nomenclature --}}
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-700 mb-4">
                <div class="text-center mb-3">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Nomenclature générée :</div>
                    <div class="font-mono text-lg font-bold text-green-800 dark:text-green-200 bg-white dark:bg-gray-800 px-4 py-2 rounded border">
                        {{ $result ?: 'Aucun paramètre actif' }}
                    </div>
                    @if($result)
                        <button 
                            onclick="navigator.clipboard.writeText('{{ $result }}')"
                            class="mt-2 text-xs text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                            title="Copier la nomenclature"
                        >
                            <x-heroicon-o-clipboard class="w-4 h-4 inline mr-1" />
                            Copier
                        </button>
                    @endif
                </div>
            </div>

            {{-- Détail des paramètres --}}
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Paramètres configurés ({{ count($data['nomenclatures']) }})
                </h4>
                @foreach($data['nomenclatures']->sortBy('order_position') as $nomenclature)
                    @php
                        $parameter = $nomenclature->festivalParameter?->parameter ?? $nomenclature->parameter;
                        $isActive = $nomenclature->is_active;
                        $isSystem = $nomenclature->festivalParameter?->is_system ?? false;
                    @endphp
                    <div class="flex items-center space-x-3 p-2 rounded-lg {{ $isActive ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-gray-100 dark:bg-gray-800 opacity-60' }}">
                        {{-- Icône du paramètre --}}
                        @if($parameter?->icon)
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                                     style="background-color: {{ match($parameter->color ?? 'gray') {
                                        'blue' => 'rgb(239, 246, 255)',
                                        'green' => 'rgb(240, 253, 244)',
                                        'purple' => 'rgb(250, 245, 255)',
                                        'orange' => 'rgb(255, 247, 237)',
                                        'indigo' => 'rgb(238, 242, 255)',
                                        'yellow' => 'rgb(254, 252, 232)',
                                        'pink' => 'rgb(253, 242, 248)',
                                        'teal' => 'rgb(240, 253, 250)',
                                        'cyan' => 'rgb(236, 254, 255)',
                                        default => 'rgb(249, 250, 251)'
                                     } }};"
                                     title="{{ $parameter->short_description ?? $parameter->name }}">
                                    <x-dynamic-component 
                                        :component="'heroicon-o-' . $parameter->icon" 
                                        class="w-4 h-4"
                                        style="color: {{ match($parameter->color ?? 'gray') {
                                            'blue' => 'rgb(59, 130, 246)',
                                            'green' => 'rgb(34, 197, 94)',
                                            'purple' => 'rgb(168, 85, 247)',
                                            'orange' => 'rgb(249, 115, 22)',
                                            'indigo' => 'rgb(99, 102, 241)',
                                            'yellow' => 'rgb(234, 179, 8)',
                                            'pink' => 'rgb(236, 72, 153)',
                                            'teal' => 'rgb(20, 184, 166)',
                                            'cyan' => 'rgb(6, 182, 212)',
                                            default => 'rgb(107, 114, 128)'
                                        } }};"
                                    />
                                </div>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-cog class="w-4 h-4 text-gray-500" />
                            </div>
                        @endif

                        {{-- Informations du paramètre --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-sm {{ $isActive ? 'text-gray-900 dark:text-white' : 'text-gray-500' }}">
                                    {{ $parameter?->name ?? 'Paramètre inconnu' }}
                                </span>
                                @if($isSystem)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                                        <x-heroicon-o-lock-closed class="w-3 h-3 mr-1" />
                                        Système
                                    </span>
                                @endif
                            </div>
                            @if($parameter?->short_description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $parameter->short_description }}</p>
                            @endif
                        </div>

                        {{-- Position et statut --}}
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="px-2 py-1 rounded-full {{ $isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $isActive ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="text-gray-400 dark:text-gray-500">{{ $nomenclature->order_position }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
