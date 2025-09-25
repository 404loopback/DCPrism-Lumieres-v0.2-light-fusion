@php
    $progress = $getState() ?? 0;
    $progressPercent = max(0, min(100, $progress));
    $colorClass = match (true) {
        $progressPercent >= 90 => 'bg-success-500',
        $progressPercent >= 70 => 'bg-primary-500',
        $progressPercent >= 50 => 'bg-warning-500',
        $progressPercent >= 25 => 'bg-orange-500',
        default => 'bg-danger-500'
    };
@endphp

<div class="w-full">
    <div class="flex items-center justify-between text-xs mb-1">
        <span class="text-gray-600 dark:text-gray-400">{{ $progressPercent }}%</span>
        @if($progressPercent === 100)
            <span class="text-success-600 dark:text-success-400">✓</span>
        @elseif($progressPercent === 0)
            <span class="text-gray-400">◯</span>
        @else
            <span class="text-primary-600 dark:text-primary-400">⟳</span>
        @endif
    </div>
    
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
        <div 
            class="{{ $colorClass }} h-2 rounded-full transition-all duration-300 ease-out relative overflow-hidden"
            style="width: {{ $progressPercent }}%"
        >
            @if($progressPercent > 0 && $progressPercent < 100)
                <!-- Animation de pulsation pour les tâches en cours -->
                <div class="absolute inset-0 bg-white opacity-20 animate-pulse"></div>
                
                <!-- Effet de brillance -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 animate-shimmer"></div>
            @endif
        </div>
    </div>
    
    @if($progressPercent > 0 && $progressPercent < 100)
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            En cours...
        </div>
    @elseif($progressPercent === 100)
        <div class="text-xs text-success-600 dark:text-success-400 mt-1">
            Terminé
        </div>
    @elseif($progressPercent === 0)
        <div class="text-xs text-gray-400 mt-1">
            En attente
        </div>
    @endif
</div>

<style>
    @keyframes shimmer {
        0% { transform: translateX(-100%) skewX(-12deg); }
        100% { transform: translateX(200%) skewX(-12deg); }
    }
    
    .animate-shimmer {
        animation: shimmer 2s infinite;
    }
</style>
