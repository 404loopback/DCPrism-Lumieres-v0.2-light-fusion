<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $festival = $this->getSelectedFestival();
        @endphp
        
        <div class="flex items-center gap-6">
            {{-- Image du festival --}}
            <div class="flex-shrink-0">
                @if($festival && $festival->image)
                    <img src="{{ Storage::url($festival->image) }}" 
                         alt="{{ $festival->name }}" 
                         class="h-20 w-20 rounded-lg object-cover shadow-md">
                @else
                    <div class="h-20 w-20 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>
            
            {{-- Informations du festival --}}
            <div class="flex-1 space-y-3">
                {{-- Sélecteur de festival --}}
                <div class="max-w-md">
                    <form wire:submit.prevent="">
                        {{ $this->form }}
                    </form>
                </div>
                
                {{-- Dates du festival sélectionné --}}
                @if($festival)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600 dark:text-gray-400">
                        @if($festival->start_date && $festival->end_date)
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($festival->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($festival->end_date)->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        
                        @if($festival->upload_deadline)
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Butoire: <strong>{{ \Carbon\Carbon::parse($festival->upload_deadline)->format('d/m/Y H:i') }}</strong></span>
                            </div>
                        @endif
                        
                        @if($festival->location)
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $festival->location }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
