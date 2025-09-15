<x-filament-panels::page>
    <div class="space-y-6">
        <div class="text-lg font-medium">Administration</div>
        
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button 
                    wire:click="changeTab('users')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'users',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'users',
                    ])>
                    <x-heroicon-o-users class="w-4 h-4 inline mr-2"/> Utilisateurs
                </button>
                <button 
                    wire:click="changeTab('festivals')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'festivals',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'festivals',
                    ])>
                    <x-heroicon-o-trophy class="w-4 h-4 inline mr-2"/> Festivals
                </button>
                <button 
                    wire:click="changeTab('langs')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'langs',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'langs',
                    ])>
                    <x-heroicon-o-language class="w-4 h-4 inline mr-2"/> Langues
                </button>
                <button 
                    wire:click="changeTab('nomenclatures')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'nomenclatures',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'nomenclatures',
                    ])>
                    <x-heroicon-o-queue-list class="w-4 h-4 inline mr-2"/> Nomenclatures
                </button>
                <button 
                    wire:click="changeTab('parameters')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'parameters',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'parameters',
                    ])>
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 inline mr-2"/> Paramètres
                </button>
                <button 
                    wire:click="changeTab('roles')"
                    @class([
                        'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'roles',
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300' => $activeTab !== 'roles',
                    ])>
                    <x-heroicon-o-shield-check class="w-4 h-4 inline mr-2"/> Rôles & Permissions
                </button>
            </nav>
        </div>
        
        <!-- Tab Content avec titres et tables intégrées -->
        <div class="mt-6">
            @if($activeTab === 'users')
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Gestion des Utilisateurs</h2>
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                {{ \Modules\Fresnel\app\Models\User::count() }} utilisateurs
                            </span>
                        </div>
                        <a href="{{ route('filament.fresnel.resources.users.create') }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors">
                            <x-heroicon-o-plus class="w-4 h-4"/>
                            Nouvel utilisateur
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gérez les comptes utilisateurs, leurs rôles et permissions d'accès au système.</p>
                </div>
            @elseif($activeTab === 'festivals')
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Gestion des Festivals</h2>
                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-700/10">
                                {{ \Modules\Fresnel\app\Models\Festival::count() }} festivals
                            </span>
                        </div>
                        <a href="{{ route('filament.fresnel.resources.festivals.create') }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors">
                            <x-heroicon-o-plus class="w-4 h-4"/>
                            Nouveau festival
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configurez les festivals de cinéma et leurs paramètres de distribution.</p>
                </div>
            @elseif($activeTab === 'langs')
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Gestion des Langues</h2>
                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                {{ \Modules\Fresnel\app\Models\Lang::count() }} langues
                            </span>
                        </div>
                        <a href="{{ route('filament.fresnel.resources.langs.create') }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors">
                            <x-heroicon-o-plus class="w-4 h-4"/>
                            Nouvelle langue
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administrez les langues disponibles pour l'audio et les sous-titres des films.</p>
                </div>
            @elseif($activeTab === 'nomenclatures')
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Gestion des Nomenclatures</h2>
                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                            {{ \Modules\Fresnel\app\Models\Nomenclature::count() }} règles
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Définissez les conventions de nommage et les standards pour vos fichiers DCP.</p>
                </div>
            @elseif($activeTab === 'parameters')
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Paramètres Globaux</h2>
                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-700/10">
                                {{ \Modules\Fresnel\app\Models\Parameter::count() }} paramètres
                            </span>
                        </div>
                        <a href="{{ route('filament.fresnel.resources.parameters.create') }}" 
                           class="inline-flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors">
                            <x-heroicon-o-plus class="w-4 h-4"/>
                            Nouveau paramètre
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configurez les paramètres généraux et les options avancées du système DCPrism.</p>
                </div>
            @elseif($activeTab === 'roles')
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Gestion des Rôles & Permissions</h2>
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10">
                            {{ \Spatie\Permission\Models\Role::count() }} rôles
                        </span>
                        <span class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-700/10">
                            Shield v4.0.2
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gérez les rôles utilisateur et leurs permissions d'accès aux panels. Les permissions personnalisées permettent de contrôler l'accès aux différents panels du système.</p>
                </div>
            @endif
            
            <div wire:key="admin-table-{{ $activeTab }}-{{ $tableKey }}">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
