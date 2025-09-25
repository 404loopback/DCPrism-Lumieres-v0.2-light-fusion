@php
    use App\Models\Movie;
@endphp

<x-filament-widgets::widget>
    @if($this->getMovies()->isEmpty())
        <div class="text-center py-12">
            <div class="mx-auto h-12 w-12 text-gray-400">
                <x-heroicon-o-film class="w-12 h-12" />
            </div>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Aucun film</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Commencez par créer votre premier film pour ce festival.
            </p>
        </div>
    @else
            <!-- Grille de cartes Filament -->
            <div class="w-full max-w-none grid gap-4" style="grid-template-columns: repeat(auto-fill, 280px); justify-content: space-evenly;">
                @foreach($this->getMovies() as $movie)
                    <x-filament::card class="relative group transition-all duration-300 hover:shadow-lg">
                        <!-- Header coloré avec statut -->
                        <div class="-m-6 mb-4 h-24 bg-gradient-to-br 
                            @switch($movie->status)
                                @case(Movie::STATUS_FILM_CREATED)
                                    from-gray-400 via-gray-500 to-gray-600
                                    @break
                                @case(Movie::STATUS_SOURCE_VALIDATED)
                                    from-yellow-400 via-yellow-500 to-orange-500
                                    @break
                                @case(Movie::STATUS_VERSIONS_VALIDATED)
                                    from-blue-400 via-blue-500 to-indigo-500
                                    @break
                                @case(Movie::STATUS_UPLOADS_OK)
                                @case(Movie::STATUS_DISTRIBUTION_OK)
                                    from-green-400 via-green-500 to-emerald-500
                                    @break
                                @case(Movie::STATUS_UPLOAD_ERROR)
                                @case(Movie::STATUS_VALIDATION_ERROR)
                                    from-red-400 via-red-500 to-red-600
                                    @break
                                @case(Movie::STATUS_VALIDATION_OK)
                                    from-indigo-400 via-purple-500 to-indigo-600
                                    @break
                                @default
                                    from-gray-400 via-gray-500 to-gray-600
                            @endswitch
                            rounded-t-xl relative overflow-hidden
                        ">
                            <!-- Overlay gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                            
                            <!-- Badge de statut -->
                            <div class="absolute top-3 right-3">
                                <x-filament::badge 
                                    :color="match($movie->status) {
                                        Movie::STATUS_FILM_CREATED => 'gray',
                                        Movie::STATUS_SOURCE_VALIDATED => 'warning', 
                                        Movie::STATUS_VERSIONS_VALIDATED => 'info',
                                        Movie::STATUS_UPLOADS_OK, Movie::STATUS_DISTRIBUTION_OK => 'success',
                                        Movie::STATUS_UPLOAD_ERROR, Movie::STATUS_VALIDATION_ERROR => 'danger',
                                        Movie::STATUS_VALIDATION_OK => 'primary',
                                        default => 'gray'
                                    }"
                                    size="xs"
                                >
                                    {{ Movie::getStatuses()[$movie->status] ?? $movie->status }}
                                </x-filament::badge>
                            </div>
                            
                            <!-- Titre en overlay -->
                            <div class="absolute bottom-3 left-3 right-3">
                                <h3 class="text-lg font-bold text-white drop-shadow-lg line-clamp-2" title="{{ $movie->title }}">
                                    {{ $movie->title }}
                                </h3>
                            </div>
                        </div>

                        <!-- Contenu -->
                        <div class="space-y-4">
                            <!-- Email source -->
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-m-at-symbol class="h-4 w-4 flex-shrink-0" />
                                <span class="truncate font-medium">{{ $movie->source_email }}</span>
                            </div>

                            <!-- Métriques en grid -->
                            <div class="grid grid-cols-3 gap-2">
                                @if($movie->duration)
                                    <div class="text-center p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                        <x-heroicon-m-clock class="h-4 w-4 text-amber-600 dark:text-amber-400 mx-auto mb-1" />
                                        <div class="text-xs font-semibold text-amber-800 dark:text-amber-200">{{ $movie->duration }}min</div>
                                    </div>
                                @endif
                                
                                <div class="text-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <x-heroicon-m-calendar class="h-4 w-4 text-blue-600 dark:text-blue-400 mx-auto mb-1" />
                                    <div class="text-xs font-semibold text-blue-800 dark:text-blue-200">{{ $movie->created_at->format('d/m') }}</div>
                                </div>
                                
                                @php $versionCount = $movie->versions->count(); @endphp
                                <div class="text-center p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <x-heroicon-m-film class="h-4 w-4 text-purple-600 dark:text-purple-400 mx-auto mb-1" />
                                    <div class="text-xs font-semibold text-purple-800 dark:text-purple-200">{{ $versionCount }}</div>
                                </div>
                            </div>

                            <!-- Types de versions -->
                            @php $versionTypes = $movie->versions->pluck('type')->unique()->take(3)->toArray(); @endphp
                            @if(count($versionTypes) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($versionTypes as $type)
                                        <x-filament::badge color="gray" size="xs">{{ $type }}</x-filament::badge>
                                    @endforeach
                                    @if($movie->versions->pluck('type')->unique()->count() > 3)
                                        <x-filament::badge color="gray" size="xs">+{{ $movie->versions->pluck('type')->unique()->count() - 3 }}</x-filament::badge>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Actions -->
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex gap-1">
                                    @if(in_array($movie->status, [Movie::STATUS_FILM_CREATED, Movie::STATUS_SOURCE_VALIDATED]))
                                        <x-filament::button
                                            color="success"
                                            size="xs"
                                            icon="heroicon-m-paper-airplane"
                                            wire:click="notifySource({{ $movie->id }})"
                                        >
                                            Notifier
                                        </x-filament::button>
                                    @endif
                                </div>

                                <div class="flex gap-1">
                                    <x-filament::button
                                        color="primary" 
                                        size="xs"
                                        icon="heroicon-m-pencil"
                                        :href="route('filament.manager.resources.movies.edit', $movie)"
                                        tag="a"
                                    >
                                        Éditer
                                    </x-filament::button>

                                    <x-filament::button
                                        color="danger"
                                        size="xs"
                                        icon="heroicon-m-trash"
                                        wire:click="deleteMovie({{ $movie->id }})"
                                        x-on:click="confirm('Êtes-vous sûr de vouloir supprimer ce film ?') || event.stopImmediatePropagation()"
                                    >
                                        Supprimer
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </x-filament::card>
                @endforeach
            </div>

        <!-- Pagination -->
        @if($this->getMovies()->hasPages())
            <div class="mt-6">
                {{ $this->getMovies()->links() }}
            </div>
        @endif
    @endif
</x-filament-widgets::widget>
