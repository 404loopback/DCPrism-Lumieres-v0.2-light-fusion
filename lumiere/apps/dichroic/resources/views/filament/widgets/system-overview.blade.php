<div class="fi-section-content">
    <!-- Summary Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="fi-wi-stat relative rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="fi-wi-stat-label text-xs font-medium text-gray-500 dark:text-gray-400">Users</p>
                    <p class="fi-wi-stat-value text-lg font-semibold text-gray-950 dark:text-white">{{ $summary['total_users'] }}</p>
                    <p class="fi-wi-stat-description text-xs text-green-600 dark:text-green-400">{{ $summary['active_users'] }} active</p>
                </div>
                <div class="fi-wi-stat-icon flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-500 dark:bg-blue-500/10 dark:text-blue-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="fi-wi-stat relative rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="fi-wi-stat-label text-xs font-medium text-gray-500 dark:text-gray-400">Jobs</p>
                    <p class="fi-wi-stat-value text-lg font-semibold text-gray-950 dark:text-white">{{ $summary['total_jobs'] }}</p>
                    <p class="fi-wi-stat-description text-xs text-green-600 dark:text-green-400">{{ $summary['completed_jobs'] }} completed</p>
                </div>
                <div class="fi-wi-stat-icon flex h-8 w-8 items-center justify-center rounded-full bg-yellow-50 text-yellow-500 dark:bg-yellow-500/10 dark:text-yellow-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16l13-8L7 4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="fi-wi-stat relative rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="fi-wi-stat-label text-xs font-medium text-gray-500 dark:text-gray-400">Configs</p>
                    <p class="fi-wi-stat-value text-lg font-semibold text-gray-950 dark:text-white">{{ $summary['total_configs'] }}</p>
                    <p class="fi-wi-stat-description text-xs text-green-600 dark:text-green-400">{{ $summary['deployed_configs'] }} deployed</p>
                </div>
                <div class="fi-wi-stat-icon flex h-8 w-8 items-center justify-center rounded-full bg-purple-50 text-purple-500 dark:bg-purple-500/10 dark:text-purple-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="fi-wi-stat relative rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="fi-wi-stat-label text-xs font-medium text-gray-500 dark:text-gray-400">Deployments</p>
                    <p class="fi-wi-stat-value text-lg font-semibold text-gray-950 dark:text-white">{{ $summary['total_deployments'] }}</p>
                    <p class="fi-wi-stat-description text-xs text-green-600 dark:text-green-400">{{ $summary['active_deployments'] }} active</p>
                </div>
                <div class="fi-wi-stat-icon flex h-8 w-8 items-center justify-center rounded-full bg-green-50 text-green-500 dark:bg-green-500/10 dark:text-green-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <!-- Recent OpenTofu Configs -->
        <div class="fi-section relative rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header px-3 py-2">
                <h3 class="fi-section-header-heading text-sm font-semibold text-gray-950 dark:text-white">Recent Configs</h3>
            </div>
            <div class="fi-section-content px-3 pb-3">
                @if($configs->count() > 0)
                    <div class="space-y-2">
                        @foreach($configs->take(3) as $config)
                            <div class="flex items-center justify-between py-1.5">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-950 dark:text-white truncate">{{ $config->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $config->provider }} • {{ $config->scenario }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 ml-2">
                                    <span class="fi-badge inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset
                                        @if($config->status === 'deployed') bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/20
                                        @elseif($config->status === 'failed') bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20
                                        @elseif($config->status === 'planned') bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/20
                                        @else bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20 @endif">
                                        {{ ucfirst($config->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">No configurations yet</p>
                @endif
            </div>
        </div>

        <!-- Recent Deployments -->
        <div class="fi-section relative rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header px-3 py-2">
                <h3 class="fi-section-header-heading text-sm font-semibold text-gray-950 dark:text-white">Recent Deployments</h3>
            </div>
            <div class="fi-section-content px-3 pb-3">
                @if($deployments->count() > 0)
                    <div class="space-y-2">
                        @foreach($deployments->take(3) as $deployment)
                            <div class="flex items-center justify-between py-1.5">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-950 dark:text-white truncate">{{ $deployment->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $deployment->scenario ?? 'N/A' }} • {{ $deployment->environment ?? 'N/A' }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 ml-2">
                                    <span class="fi-badge inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset
                                        @if($deployment->status === 'deployed') bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/20
                                        @elseif($deployment->status === 'failed') bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20
                                        @elseif($deployment->status === 'deploying') bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/20
                                        @else bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20 @endif">
                                        {{ ucfirst($deployment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">No deployments yet</p>
                @endif
            </div>
        </div>
    </div>
</div>
