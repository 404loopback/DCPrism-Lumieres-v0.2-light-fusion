<x-filament-panels::page>
    {{-- Aide contextuelle --}}
    <div class="mb-6">
        <div class="bg-blue-50 dark:bg-blue-950/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start space-x-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                <div class="text-sm">
                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-1">
                        Guide des paramètres
                    </h4>
                    <p class="text-blue-700 dark:text-blue-300 mb-2">
                        Sélectionnez les paramètres optionnels que vous souhaitez utiliser pour votre festival.
                        Les paramètres système sont automatiquement ajoutés et ne peuvent pas être supprimés.
                    </p>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-lock-closed class="w-3 h-3 text-amber-500" />
                            <span class="text-amber-700 dark:text-amber-300">Paramètre système</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-plus-circle class="w-3 h-3 text-green-500" />
                            <span class="text-green-700 dark:text-green-300">Paramètre optionnel</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-eye class="w-3 h-3 text-blue-500" />
                            <span class="text-blue-700 dark:text-blue-300">Survolez les icônes pour plus de détails</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table des paramètres --}}
    <div class="space-y-4">
        {{ $this->table }}
    </div>

    {{-- CSS pour les tooltips avancés --}}
    @push('styles')
    <style>
        /* Améliorations des tooltips */
        .fi-tooltip {
            max-width: 320px !important;
            white-space: pre-line;
        }
        
        /* Animation des icônes de paramètres */
        .fi-ta-icon-column-icon {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .fi-ta-icon-column-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Mise en évidence des paramètres système */
        .fi-ta-row.bg-yellow-50 {
            background-color: rgba(254, 252, 232, 0.5) !important;
            border-left: 3px solid rgb(245, 158, 11);
        }
        
        .dark .fi-ta-row.bg-yellow-950\/20 {
            background-color: rgba(120, 53, 15, 0.1) !important;
            border-left: 3px solid rgb(245, 158, 11);
        }
    </style>
    @endpush
</x-filament-panels::page>
