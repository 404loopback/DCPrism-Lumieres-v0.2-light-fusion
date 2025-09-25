@if(isset($message))
    <div class="p-4 text-center">
        <div class="text-gray-500 dark:text-gray-400">
            {{ $message }}
        </div>
    </div>
@else
    <div class="space-y-6">
        @foreach($previews as $festivalPreview)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Festival: {{ $festivalPreview['festival'] }}
                </h3>
                
                <div class="space-y-3">
                    <!-- Nomenclature finale -->
                    <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-md">
                        <div class="text-sm font-medium text-green-800 dark:text-green-200">
                            Nomenclature générée:
                        </div>
                        <div class="text-lg font-mono text-green-900 dark:text-green-100 mt-1">
                            {{ $festivalPreview['preview']['final_nomenclature'] }}
                        </div>
                    </div>
                    
                    <!-- Détail des parties -->
                    @if(!empty($festivalPreview['preview']['preview_parts']))
                        <div class="space-y-2">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Détail des parties:
                            </div>
                            @foreach($festivalPreview['preview']['preview_parts'] as $part)
                                <div class="flex items-center justify-between text-sm bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                    <div class="font-medium">{{ $part['parameter'] }}:</div>
                                    <div class="flex items-center space-x-2">
                                        @if($part['raw_value'])
                                            <span class="text-gray-600 dark:text-gray-400">{{ $part['raw_value'] }}</span>
                                            <span class="text-gray-400">→</span>
                                            <span class="font-mono text-blue-600 dark:text-blue-400">{{ $part['formatted_value'] }}</span>
                                        @else
                                            <span class="text-red-500 text-xs">{{ $part['is_required'] ? 'REQUIS - MANQUANT' : 'Optionnel - Non renseigné' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <!-- Avertissements -->
                    @if(!empty($festivalPreview['preview']['warnings']))
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-md">
                            <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                                ⚠️ Avertissements:
                            </div>
                            <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                @foreach($festivalPreview['preview']['warnings'] as $warning)
                                    <li>• {{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Statut de validité -->
                    <div class="flex items-center space-x-2 text-sm">
                        @if($festivalPreview['preview']['is_valid'])
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                                ✓ Valide
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
                                ✗ Incomplète
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        <!-- Actions recommandées -->
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <div class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                💡 Actions recommandées:
            </div>
            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                <li>• Configurer les paramètres manquants dans l'onglet Paramètres du film</li>
                <li>• Utiliser l'action "Générer Nomenclature" pour appliquer automatiquement</li>
                <li>• Vérifier la configuration de nomenclature du festival si nécessaire</li>
            </ul>
        </div>
    </div>
@endif
