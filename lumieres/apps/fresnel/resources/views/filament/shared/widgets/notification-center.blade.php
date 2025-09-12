<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-bell class="h-5 w-5" />
                    Centre de notifications
                    @if($unread_count > 0)
                        <x-filament::badge color="danger" size="xs">
                            {{ $unread_count }}
                        </x-filament::badge>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($unread_count > 0)
                        <x-filament::button 
                            size="xs" 
                            color="gray"
                            wire:click="markAllAsRead"
                            wire:loading.attr="disabled"
                        >
                            Marquer tout comme lu
                        </x-filament::button>
                    @endif
                    @if($stats['read'] > 0)
                        <x-filament::button 
                            size="xs" 
                            color="danger"
                            wire:click="clearReadNotifications"
                            wire:loading.attr="disabled"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer toutes les notifications lues ?"
                        >
                            Vider les lues
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </x-slot>

        <!-- Statistiques -->
        @if($stats['total'] > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                    <div class="text-sm text-red-600 dark:text-red-400">Non lues</div>
                    <div class="text-lg font-semibold text-red-700 dark:text-red-300">{{ $stats['unread'] }}</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                    <div class="text-sm text-blue-600 dark:text-blue-400">Aujourd'hui</div>
                    <div class="text-lg font-semibold text-blue-700 dark:text-blue-300">{{ $stats['today'] }}</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                    <div class="text-sm text-green-600 dark:text-green-400">Taux de lecture</div>
                    <div class="text-lg font-semibold text-green-700 dark:text-green-300">{{ $stats['read_rate'] }}%</div>
                </div>
            </div>
        @endif

        <!-- Liste des notifications -->
        @if($notifications->count() > 0)
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = is_null($notification->read_at);
                        $iconColor = $isUnread ? 'text-primary-500' : 'text-gray-400';
                        $bgColor = $isUnread ? 'bg-primary-50 dark:bg-primary-900/10' : 'bg-gray-50 dark:bg-gray-800';
                    @endphp
                    
                    <div class="flex items-start gap-3 p-4 rounded-lg border {{ $bgColor }} {{ $isUnread ? 'border-primary-200 dark:border-primary-800' : 'border-gray-200 dark:border-gray-700' }}">
                        <!-- Icône de notification -->
                        <div class="flex-shrink-0 mt-1">
                            @if(isset($data['icon']))
                                <x-dynamic-component :component="$data['icon']" class="h-5 w-5 {{ $iconColor }}" />
                            @else
                                <x-heroicon-o-bell class="h-5 w-5 {{ $iconColor }}" />
                            @endif
                        </div>

                        <!-- Contenu de la notification -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium {{ $isUnread ? 'text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $data['title'] ?? 'Notification' }}
                                    </h4>
                                    @if(isset($data['message']) || isset($data['body']))
                                        <p class="mt-1 text-sm {{ $isUnread ? 'text-gray-600 dark:text-gray-400' : 'text-gray-500 dark:text-gray-500' }}">
                                            {{ $data['message'] ?? $data['body'] ?? '' }}
                                        </p>
                                    @endif
                                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if($notification->created_at->isToday())
                                            à {{ $notification->created_at->format('H:i') }}
                                        @endif
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1 ml-4">
                                    @if($isUnread)
                                        <x-filament::icon-button
                                            icon="heroicon-m-check"
                                            size="sm"
                                            color="success"
                                            tooltip="Marquer comme lu"
                                            wire:click="markAsRead('{{ $notification->id }}')"
                                        />
                                    @endif
                                    
                                    <x-filament::icon-button
                                        icon="heroicon-m-trash"
                                        size="sm"
                                        color="danger"
                                        tooltip="Supprimer"
                                        wire:click="deleteNotification('{{ $notification->id }}')"
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette notification ?"
                                    />
                                </div>
                            </div>

                            <!-- Actions supplémentaires de la notification -->
                            @if(isset($data['actions']) && is_array($data['actions']))
                                <div class="mt-3 flex gap-2">
                                    @foreach($data['actions'] as $action)
                                        <x-filament::button
                                            :href="$action['url'] ?? '#'"
                                            size="xs"
                                            :color="$action['color'] ?? 'primary'"
                                            target="{{ $action['target'] ?? '_self' }}"
                                        >
                                            {{ $action['label'] }}
                                        </x-filament::button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($stats['total'] > 10)
                <div class="mt-4 text-center">
                    <x-filament::button
                        color="gray"
                        size="sm"
                        outlined
                    >
                        Voir toutes les notifications ({{ $stats['total'] - 10 }} de plus)
                    </x-filament::button>
                </div>
            @endif
        @else
            <!-- Aucune notification -->
            <div class="text-center py-8">
                <x-heroicon-o-bell-slash class="h-12 w-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Aucune notification
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Vous n'avez aucune notification pour le moment.
                </p>
            </div>
        @endif

        <!-- Contexte festival si disponible -->
        @if($festival_context['has_filter'] ?? false)
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-film class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span class="text-sm text-blue-800 dark:text-blue-200">
                        Contexte : {{ $festival_context['name'] }}
                    </span>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
