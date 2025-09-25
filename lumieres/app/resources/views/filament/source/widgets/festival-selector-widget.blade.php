<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between space-x-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Festival actuel : {{ $this->getSelectedFestivalName() }}
            </h3>
            <div class="min-w-0 flex-1 max-w-sm">
                <form wire:submit.prevent="">
                    {{ $this->form }}
                </form>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
