<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Formulaire de configuration -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Configuration de la nomenclature
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Définissez les règles de nomenclature pour vos films DCP
                </p>
            </div>
            
            <div class="px-6 py-4">
                {{ $this->form }}
            </div>
            
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <button 
                        wire:click="generatePreview" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Prévisualiser
                    </button>
                    
                    <button 
                        wire:click="generateNomenclature" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Sauvegarder les règles
                    </button>
                </div>
            </div>
        </div>

        <!-- Prévisualisation -->
        @if($generatedNomenclature)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Prévisualisation de la nomenclature
                </h3>
            </div>
            
            <div class="px-6 py-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Résultat généré</h3>
                            <div class="mt-2">
                                <p class="text-2xl font-mono font-semibold text-blue-900 dark:text-blue-100 break-all">
                                    {{ $generatedNomenclature }}
                                </p>
                                <button 
                                    onclick="copyToClipboard('{{ addslashes($generatedNomenclature) }}')"
                                    class="mt-2 inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    Copier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Guide d'aide -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Guide d'utilisation
                </h3>
            </div>
            
            <div class="px-6 py-4">
                <div class="prose dark:prose-invert max-w-none">
                    <h4>Comment utiliser le générateur de nomenclature :</h4>
                    
                    <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li><strong>Sélectionnez un festival</strong> - Chaque festival peut avoir sa propre nomenclature</li>
                        <li><strong>Ajoutez des paramètres</strong> - Utilisez le bouton "+" pour ajouter des éléments à votre nomenclature</li>
                        <li><strong>Définissez l'ordre</strong> - Le champ "Position" détermine l'ordre des éléments</li>
                        <li><strong>Configurez les séparateurs</strong> - Définissez comment les éléments sont séparés (_, -, etc.)</li>
                        <li><strong>Ajustez les options</strong> - Transformations de casse, suppression d'accents, etc.</li>
                        <li><strong>Prévisualisez</strong> - Utilisez le bouton "Prévisualiser" pour voir le résultat</li>
                        <li><strong>Sauvegardez</strong> - Les règles seront appliquées automatiquement aux nouveaux films</li>
                    </ol>
                    
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Conseil</h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>Vous pouvez sélectionner un film existant pour tester votre nomenclature avec de vraies données.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="mt-6">Exemples de nomenclature :</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-300">
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">CANNES2024_MonFilm_DCP</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">festival-film-2024-version1</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">FF_LeGrandFilm_V2_2K_FR</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Notification de succès
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                notification.textContent = 'Nomenclature copiée dans le presse-papiers !';
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }).catch(function(err) {
                console.error('Erreur copie:', err);
                alert('Erreur lors de la copie');
            });
        }
        
        // Auto-prévisualisation quand les champs changent
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ component }) => {
                // Auto-générer la prévisualisation quand le formulaire change
                if (component.name === 'nomenclature-generator') {
                    setTimeout(() => {
                        @this.call('generatePreview');
                    }, 500);
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
