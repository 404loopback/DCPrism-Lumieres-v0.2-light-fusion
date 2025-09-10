{{-- Vue récapitulative des données du film dans le wizard --}}

<div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        Récapitulatif du film
    </h3>

    {{-- Section Informations générales --}}
    <div class="space-y-2">
        <h4 class="font-medium text-gray-800 dark:text-gray-200">Informations générales</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div class="flex flex-col">
                <span class="text-gray-500 dark:text-gray-400">Titre :</span>
                <span class="font-medium" x-text="$wire.data.title || 'Non renseigné'"></span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500 dark:text-gray-400">Email source :</span>
                <span class="font-medium" x-text="$wire.data.source_email || 'Non renseigné'"></span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500 dark:text-gray-400">Durée :</span>
                <span class="font-medium" x-text="($wire.data.duration ? $wire.data.duration + ' minutes' : 'Non renseigné')"></span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500 dark:text-gray-400">Année :</span>
                <span class="font-medium" x-text="$wire.data.year || 'Non renseigné'"></span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500 dark:text-gray-400">Pays :</span>
                <span class="font-medium" x-text="$wire.data.country || 'Non renseigné'"></span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500 dark:text-gray-400">Genre :</span>
                <span class="font-medium" x-text="$wire.data.genre || 'Non renseigné'"></span>
            </div>
        </div>
        <div class="flex flex-col" x-show="$wire.data.description">
            <span class="text-gray-500 dark:text-gray-400">Synopsis :</span>
            <div class="mt-1 p-2 bg-white dark:bg-gray-700 rounded border text-sm" x-text="$wire.data.description"></div>
        </div>
    </div>

    {{-- Section Versions --}}
    <div class="space-y-2" x-show="$wire.data.versions && $wire.data.versions.length > 0">
        <h4 class="font-medium text-gray-800 dark:text-gray-200">Versions créées</h4>
        <div class="space-y-2">
            <template x-for="(version, index) in $wire.data.versions" :key="index">
                <div class="p-3 bg-white dark:bg-gray-700 rounded border">
                    <div class="flex justify-between items-center">
                        <span class="font-medium" x-text="version.type || 'Version ' + (index + 1)"></span>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="version.audio_lang || 'Audio: Non défini'"></span>
                            <span x-show="version.sub_lang && version.sub_lang !== 'none'"> • ST: </span>
                            <span x-show="version.sub_lang && version.sub_lang !== 'none'" x-text="version.sub_lang"></span>
                            <span x-show="version.accessibility && version.accessibility !== 'none'"> • </span>
                            <span x-show="version.accessibility && version.accessibility !== 'none'" x-text="version.accessibility"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Message si aucune version --}}
    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded" 
         x-show="!$wire.data.versions || $wire.data.versions.length === 0">
        <p class="text-blue-700 dark:text-blue-300 text-sm">
            <strong>Génération automatique :</strong> Aucune version manuelle définie. 
            Des versions seront générées automatiquement selon les paramètres du festival.
        </p>
    </div>

    {{-- Section Paramètres (si renseignés) --}}
    <div class="space-y-2" x-show="hasParameters()">
        <h4 class="font-medium text-gray-800 dark:text-gray-200">Paramètres configurés</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <template x-for="[key, value] in Object.entries($wire.data)" :key="key">
                <div class="flex flex-col" x-show="key.startsWith('parameter_') && value">
                    <span class="text-gray-500 dark:text-gray-400" x-text="key.replace('parameter_', 'Paramètre ') + ' :'"></span>
                    <span class="font-medium" x-text="value"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Note de confirmation --}}
    <div class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="text-green-700 dark:text-green-300 text-sm font-medium">
                    Prêt pour la création
                </p>
                <p class="text-green-600 dark:text-green-400 text-xs mt-1">
                    Cliquez sur "Créer le film" pour finaliser la création. 
                    Vous pourrez modifier ces informations après création si nécessaire.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function hasParameters() {
        return Object.keys(this.$wire.data).some(key => key.startsWith('parameter_') && this.$wire.data[key]);
    }
</script>
