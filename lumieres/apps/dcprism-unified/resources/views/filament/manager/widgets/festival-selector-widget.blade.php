<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $label }}
        </x-slot>
        
        <div class="space-y-4">
            <form wire:submit.prevent="">
                {{ $this->form }}
            </form>
            
            @if($selectedFestivalId)
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>Festival actuel :</strong> {{ $this->getSelectedFestivalName() }}
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
