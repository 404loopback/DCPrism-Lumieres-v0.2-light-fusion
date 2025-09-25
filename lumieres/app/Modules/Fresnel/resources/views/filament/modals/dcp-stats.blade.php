<x-filament::section icon="heroicon-o-chart-bar" icon-color="info">
    <x-slot name="heading">Vue d'ensemble</x-slot>
    
    
    <!-- Grille des statistiques avec CSS inline -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
        <!-- Total DCPs -->
        <div class="stat-card-primary">
            <div class="stat-icon">
                <div class="stat-icon-bg">
                    <x-filament::icon icon="heroicon-s-film" class="h-5 w-5" />
                </div>
            </div>
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total DCPs</div>
        </div>
        
        <!-- Validés -->
        <div class="stat-card-success">
            <div class="stat-icon">
                <div class="stat-icon-bg">
                    <x-filament::icon icon="heroicon-s-check-circle" class="h-5 w-5" />
                </div>
            </div>
            <div class="stat-number">{{ $stats['valid'] }}</div>
            <div class="stat-label">Validés</div>
        </div>
        
        <!-- En attente -->
        <div class="stat-card-warning">
            <div class="stat-icon">
                <div class="stat-icon-bg">
                    <x-filament::icon icon="heroicon-s-clock" class="h-5 w-5" />
                </div>
            </div>
            <div class="stat-number">{{ $stats['pending'] }}</div>
            <div class="stat-label">En attente</div>
        </div>
        
        <!-- Invalides -->
        <div class="stat-card-danger">
            <div class="stat-icon">
                <div class="stat-icon-bg">
                    <x-filament::icon icon="heroicon-s-x-circle" class="h-5 w-5" />
                </div>
            </div>
            <div class="stat-number">{{ $stats['invalid'] }}</div>
            <div class="stat-label">Invalides</div>
        </div>
        
        <!-- En traitement -->
        <div class="stat-card-info">
            <div class="stat-icon">
                <div class="stat-icon-bg">
                    <x-filament::icon icon="heroicon-s-cog-6-tooth" class="h-5 w-5 animate-spin" />
                </div>
            </div>
            <div class="stat-number">{{ $stats['processing'] }}</div>
            <div class="stat-label">En traitement</div>
        </div>
        
        <!-- Erreurs -->
        <div class="stat-card-gray">
            <div class="stat-icon">
                <div class="stat-icon-bg">
                    <x-filament::icon icon="heroicon-s-exclamation-triangle" class="h-5 w-5" />
                </div>
            </div>
            <div class="stat-number">{{ $stats['error'] }}</div>
            <div class="stat-label">Erreurs</div>
        </div>
    </div>
</x-filament::section>

@if(!empty($versionStats))
<x-filament::section icon="heroicon-o-film" icon-color="primary">
    <x-slot name="heading">Répartition par version</x-slot>
    
    <div class="space-y-3">
        @foreach($versionStats as $type => $count)
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-filament::badge 
                    color="{{ $type === 'VO' ? 'info' : ($type === 'VF' ? 'success' : ($type === 'VOST' ? 'primary' : ($type === 'VOSTF' ? 'warning' : 'gray'))) }}"
                >
                    {{ $type }}
                </x-filament::badge>
                <span class="text-sm">
                    {{ $count }} DCP{{ $count > 1 ? 's' : '' }}
                </span>
            </div>
            
            @if($stats['total'] > 0)
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ round(($count / $stats['total']) * 100, 1) }}%
            </span>
            @endif
        </div>
        @endforeach
    </div>
</x-filament::section>
@endif

@if($stats['total'] > 0)
<x-filament::section icon="heroicon-o-chart-pie" icon-color="success">
    <x-slot name="heading">Taux de validation</x-slot>
    
    <div class="space-y-4">
        @php
            $validationRate = ($stats['valid'] / $stats['total']) * 100;
            $progressColor = $validationRate >= 80 ? 'success' : ($validationRate >= 50 ? 'warning' : 'danger');
        @endphp
        
        <!-- Progress bar améliorée -->
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progression</span>
                <span class="text-lg font-bold text-{{ $progressColor }}-600 dark:text-{{ $progressColor }}-400">
                    {{ round($validationRate, 1) }}%
                </span>
            </div>
            
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 shadow-inner">
                <div class="bg-gradient-to-r from-{{ $progressColor }}-400 to-{{ $progressColor }}-600 h-4 rounded-full transition-all duration-500 ease-out shadow-sm" 
                     style="width: {{ $validationRate }}%"></div>
            </div>
        </div>
        
        <!-- Détails avec icônes -->
        <div class="bg-gradient-to-r from-success-50 to-green-50 dark:from-success-900/10 dark:to-green-900/10 p-3 rounded-lg border border-success-200 dark:border-success-800">
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-s-check-circle" class="h-4 w-4 text-success-600 dark:text-success-400" />
                <span class="text-sm font-medium text-success-800 dark:text-success-200">
                    {{ $stats['valid'] }} DCPs validés sur {{ $stats['total'] }} au total
                </span>
            </div>
        </div>
        
        @if($validationRate < 100)
        <div class="bg-gradient-to-r from-warning-50 to-orange-50 dark:from-warning-900/10 dark:to-orange-900/10 p-3 rounded-lg border border-warning-200 dark:border-warning-800">
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-s-clock" class="h-4 w-4 text-warning-600 dark:text-warning-400" />
                <span class="text-sm text-warning-800 dark:text-warning-200">
                    {{ $stats['total'] - $stats['valid'] }} DCPs restants à valider
                </span>
            </div>
        </div>
        @endif
    </div>
</x-filament::section>
@endif

@if($stats['pending'] > 0 || $stats['invalid'] > 0 || $stats['error'] > 0)
<x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="warning">
    <x-slot name="heading">Actions suggérées</x-slot>
    
    <div class="space-y-2">
        @if($stats['pending'] > 0)
        <div class="flex items-center space-x-2">
            <x-filament::badge color="warning">{{ $stats['pending'] }}</x-filament::badge>
            <span class="text-sm">DCPs en attente de validation</span>
        </div>
        @endif
        
        @if($stats['invalid'] > 0)
        <div class="flex items-center space-x-2">
            <x-filament::badge color="danger">{{ $stats['invalid'] }}</x-filament::badge>
            <span class="text-sm">DCPs nécessitent une attention particulière</span>
        </div>
        @endif
        
        @if($stats['error'] > 0)
        <div class="flex items-center space-x-2">
            <x-filament::badge color="gray">{{ $stats['error'] }}</x-filament::badge>
            <span class="text-sm">DCPs ont des erreurs techniques</span>
        </div>
        @endif
    </div>
</x-filament::section>
@endif
