<x-filament-panels::page>
    <div class="space-y-6">
        <div class="text-lg font-medium">Gestion Films</div>
        
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button 
                    wire:click="changeTab('films')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'films',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'films',
                    ])>
                    <x-heroicon-o-film class="w-4 h-4 inline mr-2"/> Films
                </button>
                <button 
                    wire:click="changeTab('versions')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'versions',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'versions',
                    ])>
                    <x-heroicon-o-document-duplicate class="w-4 h-4 inline mr-2"/> Versions
                </button>
                <button 
                    wire:click="changeTab('dcps')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'dcps',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'dcps',
                    ])>
                    <x-heroicon-o-archive-box class="w-4 h-4 inline mr-2"/> DCPs
                </button>
                <button 
                    wire:click="changeTab('metadata')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'metadata',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'metadata',
                    ])>
                    <x-heroicon-o-tag class="w-4 h-4 inline mr-2"/> Métadonnées
                </button>
            </nav>
        </div>
        
        <!-- Tab Content avec titres et tables intégrées -->
        <div class="mt-6">
            @if($activeTab === 'films')
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bibliothèque de Films</h2>
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            {{ \Modules\Fresnel\app\Models\Movie::count() }} films
                        </span>
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                            {{ \Modules\Fresnel\app\Models\Movie::whereNotNull('validated_at')->count() }} validés
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gestion complète de votre catalogue de films DCP : upload, validation et suivi des statuts.</p>
                </div>
            @elseif($activeTab === 'versions')
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Versions de Films</h2>
                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                            {{ \Modules\Fresnel\app\Models\Version::count() }} versions
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gérez les différentes versions : Feature (FTR), Short (SHR), Trailer (TRL) et leurs variantes linguistiques.</p>
                </div>
            @elseif($activeTab === 'dcps')
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Packages DCP</h2>
                        <span class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-700/10">
                            {{ \Modules\Fresnel\app\Models\Dcp::count() }} DCPs
                        </span>
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                            {{ \Modules\Fresnel\app\Models\Dcp::where('is_valid', true)->count() }} validés
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Consultez et gérez les packages DCP générés, prêts pour la distribution en salle.</p>
                </div>
            @elseif($activeTab === 'metadata')
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Métadonnées Films</h2>
                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                            {{ \Modules\Fresnel\app\Models\Movie::whereNotNull('genre')->count() }} avec genre
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Informations techniques et artistiques complémentaires des films : genres, années, classifications.</p>
                </div>
            @endif
            
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
