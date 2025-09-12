<x-filament-panels::page>
    <div class="space-y-6">
        <!-- En-tête avec informations système -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        🎬 Administration DCPrism
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Monitoring et gestion centralisée de la plateforme DCP
                    </p>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Statut système -->
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Système opérationnel
                        </span>
                    </div>
                    
                    <!-- Dernière mise à jour -->
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Dernière MAJ: {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Widgets de métriques principales -->
        <div>
            {{ $this->getHeaderWidgetsGrid() }}
        </div>

        <!-- Alertes et notifications système -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Alertes critiques -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Alertes Critiques
                    </h3>
                </div>
                
                <div class="space-y-3">
                    @php
                        $failedJobs = \App\Models\Job::where('status', 'failed')
                                                   ->where('created_at', '>=', now()->subDay())
                                                   ->count();
                        $diskUsage = 75; // Simulation - en réalité calculé dynamiquement
                        $queueDelay = 30; // Simulation - délai moyen de la queue en secondes
                    @endphp
                    
                    @if($failedJobs > 5)
                        <div class="flex items-center text-sm text-red-600 dark:text-red-400">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                            {{ $failedJobs }} jobs échoués dans les dernières 24h
                        </div>
                    @endif
                    
                    @if($diskUsage > 80)
                        <div class="flex items-center text-sm text-orange-600 dark:text-orange-400">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                            Stockage à {{ $diskUsage }}% de capacité
                        </div>
                    @endif
                    
                    @if($queueDelay > 60)
                        <div class="flex items-center text-sm text-yellow-600 dark:text-yellow-400">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            Queue en retard de {{ $queueDelay }}s
                        </div>
                    @endif
                    
                    @if($failedJobs <= 5 && $diskUsage <= 80 && $queueDelay <= 60)
                        <div class="flex items-center text-sm text-green-600 dark:text-green-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Aucune alerte critique
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Actions Rapides
                    </h3>
                </div>
                
                <div class="space-y-3">
                    <a href="{{ route('filament.admin.resources.jobs.index', ['tableFilters[status][value]' => 'failed']) }}" 
                       class="flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        Relancer les jobs échoués
                    </a>
                    
                    <a href="{{ route('filament.admin.resources.movies.index') }}" 
                       class="flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        Gérer les films
                    </a>
                    
                    <a href="{{ route('filament.admin.resources.users.index') }}" 
                       class="flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        Gérer les utilisateurs
                    </a>
                    
                    <button onclick="window.location.reload()" 
                            class="flex items-center text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        Actualiser les données
                    </button>
                </div>
            </div>

            <!-- Informations système -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Informations Système
                    </h3>
                </div>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Version:</span>
                        <span class="text-gray-900 dark:text-white font-medium">v{{ config('app.version', '1.0.0') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Laravel:</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ app()->version() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">PHP:</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ PHP_VERSION }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Base de données:</span>
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ config('database.default') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Cache:</span>
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ config('cache.default') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Queue:</span>
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ config('queue.default') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widgets de performance et monitoring -->
        <div>
            {{ $this->getFooterWidgetsGrid() }}
        </div>

        <!-- Liens utiles -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                🔗 Liens Utiles
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/api/documentation" target="_blank" 
                   class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                    <div>
                        <div class="text-sm font-medium text-blue-900 dark:text-blue-100">API Docs</div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">Documentation</div>
                    </div>
                </a>
                
                <a href="/telescope" target="_blank" 
                   class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <div class="text-sm font-medium text-purple-900 dark:text-purple-100">Telescope</div>
                        <div class="text-xs text-purple-600 dark:text-purple-400">Debug</div>
                    </div>
                </a>
                
                <a href="/horizon" target="_blank" 
                   class="flex items-center p-3 bg-orange-50 dark:bg-orange-900/30 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/50 transition-colors">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <div class="text-sm font-medium text-orange-900 dark:text-orange-100">Horizon</div>
                        <div class="text-xs text-orange-600 dark:text-orange-400">Queues</div>
                    </div>
                </a>
                
                <a href="{{ route('filament.admin.resources.activity-logs.index') }}" 
                   class="flex items-center p-3 bg-green-50 dark:bg-green-900/30 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <div class="text-sm font-medium text-green-900 dark:text-green-100">Logs</div>
                        <div class="text-xs text-green-600 dark:text-green-400">Activité</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
