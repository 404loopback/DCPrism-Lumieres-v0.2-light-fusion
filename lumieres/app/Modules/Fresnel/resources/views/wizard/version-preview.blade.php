{{-- 
    Vue de prévisualisation pour les versions de films dans le wizard
    Affiche en temps réel la nomenclature qui sera générée
--}}

<div class="space-y-6" x-data="versionPreview" x-init="init()">
    {{-- En-tête de prévisualisation --}}
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">
            <span class="mr-2">🎬</span>
            Prévisualisation de la Version
        </h3>
        <div class="flex items-center space-x-2">
            <button @click="updatePreview()" class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                Actualiser
            </button>
            <div x-show="loading" class="animate-spin h-4 w-4 border-2 border-primary-500 border-t-transparent rounded-full"></div>
            <span x-show="loading" class="text-sm text-gray-500">Génération...</span>
        </div>
    </div>

    {{-- État de validation --}}
    <template x-if="previewData">
        <div x-show="!previewData.is_valid" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-yellow-800">Configuration incomplète</h4>
                    <template x-if="previewData.warnings && previewData.warnings.length > 0">
                        <ul class="mt-1 text-sm text-yellow-700 list-disc list-inside">
                            <template x-for="warning in previewData.warnings">
                                <li x-text="warning"></li>
                            </template>
                        </ul>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- Nomenclature générée --}}
    <div class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
        <div class="text-center">
            <div class="text-sm font-medium text-gray-500 mb-2">Nomenclature qui sera générée :</div>
            <div class="text-xl font-mono font-bold text-gray-900 bg-white px-4 py-3 rounded-md border">
                <span x-text="nomenclature" class="break-all">En attente de la saisie des paramètres...</span>
            </div>
        </div>
    </div>

    {{-- Détail des composants de la nomenclature --}}
    <template x-if="previewData && previewData.preview_parts && previewData.preview_parts.length > 0">
        <div class="space-y-4">
            <h4 class="text-md font-medium text-gray-900">Détail de la construction :</h4>
            <div class="grid gap-3">
                <template x-for="(part, index) in previewData.preview_parts" :key="index">
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-xs font-semibold text-blue-600" 
                                     x-text="part.order || (index + 1)">
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900" x-text="part.parameter"></div>
                                <div class="text-xs text-gray-500" x-text="part.raw_value || 'Non défini'"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-mono text-gray-900" x-text="part.formatted_value || '—'"></div>
                            <div class="flex items-center space-x-1">
                                <template x-if="part.prefix">
                                    <span class="text-xs bg-gray-100 px-1 rounded" x-text="'Préfixe: ' + part.prefix"></span>
                                </template>
                                <template x-if="part.suffix">
                                    <span class="text-xs bg-gray-100 px-1 rounded" x-text="'Suffixe: ' + part.suffix"></span>
                                </template>
                                <template x-if="part.is_required">
                                    <span class="text-xs bg-red-100 text-red-600 px-1 rounded">Obligatoire</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Attributs de version déterminés --}}
    <template x-if="versionAttributes">
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h4 class="text-sm font-medium text-blue-900 mb-3">Attributs de la version déterminés :</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-blue-600">Type</div>
                    <div class="text-sm font-mono" x-text="versionAttributes.type">VO</div>
                </div>
                <div>
                    <div class="text-xs text-blue-600">Langue Audio</div>
                    <div class="text-sm font-mono" x-text="versionAttributes.audio_lang">original</div>
                </div>
                <div>
                    <div class="text-xs text-blue-600">Sous-titres</div>
                    <div class="text-sm font-mono" x-text="versionAttributes.sub_lang || '—'">—</div>
                </div>
                <div>
                    <div class="text-xs text-blue-600">Format</div>
                    <div class="text-sm font-mono" x-text="versionAttributes.format">FTR</div>
                </div>
            </div>
        </div>
    </template>

    {{-- Message d'erreur --}}
    <template x-if="error">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-red-800">Erreur de prévisualisation</h4>
                    <p class="mt-1 text-sm text-red-700" x-text="error"></p>
                </div>
            </div>
        </div>
    </template>

    {{-- État vide --}}
    <template x-if="!previewData && !loading && !error">
        <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.122 2.122" />
            </svg>
            <p class="mt-2">Saisissez les paramètres ci-dessus pour voir la prévisualisation</p>
        </div>
    </template>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('versionPreview', () => ({
        previewData: null,
        versionAttributes: null,
        nomenclature: 'En attente...',
        loading: false,
        error: null,

        init() {
            // Écouter les changements de paramètres
            this.updatePreview();
        },

        async updatePreview() {
            console.log('=== updatePreview() appelée ===');
            this.loading = true;
            this.error = null;

            try {
                // Récupérer les valeurs des paramètres depuis le formulaire
                const parameterValues = this.getParameterValues();
                console.log('Paramètres:', parameterValues);
                const movieTitle = this.getMovieTitle();
                console.log('Titre récupéré:', movieTitle);

                if (!movieTitle) {
                    this.nomenclature = 'Veuillez saisir le titre du film dans l\'étape 1';
                    this.loading = false;
                    return;
                }

                // Appel API pour prévisualisation
                const response = await fetch('/manager/movies/preview-version', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        title: movieTitle,
                        parameters: parameterValues
                    })
                });

                if (!response.ok) {
                    throw new Error('Erreur de communication avec le serveur');
                }

                const data = await response.json();

                if (data.success) {
                    this.previewData = data.preview;
                    this.versionAttributes = data.version_attributes;
                    this.nomenclature = data.preview.nomenclature || 'Génération échouée';
                } else {
                    this.error = data.message || 'Erreur inconnue';
                    this.nomenclature = 'Erreur de génération';
                }

            } catch (err) {
                this.error = err.message;
                this.nomenclature = 'Erreur de génération';
            } finally {
                this.loading = false;
            }
        },

        getParameterValues() {
            const values = {};
            // Récupérer toutes les valeurs des champs parameter_*
            const paramInputs = document.querySelectorAll('[name^="parameter_"]');
            paramInputs.forEach(input => {
                const paramName = input.name.replace('parameter_', '');
                values[paramName] = input.value;
            });
            return values;
        },

        getMovieTitle() {
            // Chercher le champ titre dans les paramètres (parameter_X) et dans les champs standards
            const titleSelectors = [
                '[name="movie_title_for_js"]',          // Notre champ caché spécial
                '[name^="parameter_"][name*="title"]',  // Champ paramètre titre
                '[name="title"]',                        // Champ titre standard
                'input[value][disabled]',                // Champ désactivé avec valeur (notre champ titre readonly)
                '[wire\\:model="data.title"]',           // Livewire data binding
                'input[id*="title"]',                   // ID contenant title
            ];
            
            for (const selector of titleSelectors) {
                const input = document.querySelector(selector);
                if (input && input.value && input.value.trim() !== '') {
                    console.log('Titre trouvé avec sélecteur:', selector, 'valeur:', input.value);
                    return input.value.trim();
                }
            }
            
            // Fallback: chercher dans les données Livewire
            if (window.Livewire) {
                const livewireComponent = window.Livewire.first();
                if (livewireComponent && livewireComponent.data && livewireComponent.data.title) {
                    console.log('Titre trouvé dans Livewire:', livewireComponent.data.title);
                    return livewireComponent.data.title;
                }
            }
            
            console.log('Aucun titre trouvé');
            return '';
        }
    }))
});
</script>
