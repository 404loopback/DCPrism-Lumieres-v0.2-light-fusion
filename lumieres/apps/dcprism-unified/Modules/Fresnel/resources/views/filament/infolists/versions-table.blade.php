@php
    $versions = $getState()['versions'] ?? collect();
@endphp

@if($versions->count() > 0)
    <div class="overflow-hidden bg-white shadow-sm rounded-xl border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Langue Audio
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Sous-titres
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            DCP Disponibles
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            DCP Valides
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Statut
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($versions as $version)
                        @php
                            $totalDcps = $version->dcps->count();
                            $validDcps = $version->dcps->where('is_valid', true)->count();
                            $invalidDcps = $version->dcps->where('is_valid', false)->count();
                            
                            // Logique de statut pour les versions
                            if ($totalDcps === 0) {
                                $status = 'pending';
                                $statusLabel = 'En attente DCP';
                                $statusColor = 'gray';
                            } elseif ($validDcps > 0) {
                                $status = 'validated';
                                $statusLabel = 'Validé';
                                $statusColor = 'green';
                            } elseif ($invalidDcps > 0 && $validDcps === 0) {
                                $status = 'rejected';
                                $statusLabel = 'DCP rejetés';
                                $statusColor = 'red';
                            } else {
                                $status = 'processing';
                                $statusLabel = 'En cours';
                                $statusColor = 'yellow';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $version->type }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $version->audioLanguage?->name ?? $version->audio_lang ?? 'Non spécifiée' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $version->subtitleLanguage?->name ?? $version->sub_lang ?? 'Aucun' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $totalDcps }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($validDcps > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $validDcps }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        0
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($statusColor === 'green') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($statusColor === 'red') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($statusColor === 'yellow') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                    @endif">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p>Aucune version créée pour ce film</p>
    </div>
@endif
