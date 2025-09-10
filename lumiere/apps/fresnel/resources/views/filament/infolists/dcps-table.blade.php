@php
    $dcps = $getState()['dcps'] ?? collect();
@endphp

@if($dcps->count() > 0)
    <div class="overflow-hidden bg-white shadow-sm rounded-xl border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Version
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Statut Validation
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Taille
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Uploadé par
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date Upload
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Notes Validation
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($dcps as $dcp)
                        @php
                            // Déterminer le statut et la couleur
                            if ($dcp->is_valid === null || $dcp->status === 'uploaded' || $dcp->status === 'processing') {
                                $statusLabel = 'En attente';
                                $statusColor = 'yellow';
                            } elseif ($dcp->is_valid === true && $dcp->status === 'valid') {
                                $statusLabel = 'Validé';
                                $statusColor = 'green';
                            } elseif ($dcp->is_valid === false && $dcp->status === 'invalid') {
                                $statusLabel = 'Rejeté';
                                $statusColor = 'red';
                            } elseif ($dcp->status === 'error') {
                                $statusLabel = 'Erreur';
                                $statusColor = 'red';
                            } else {
                                $statusLabel = 'Inconnu';
                                $statusColor = 'gray';
                            }
                            
                            // Formater la taille du fichier
                            $fileSize = 'Inconnue';
                            if ($dcp->file_size) {
                                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                $bytes = $dcp->file_size;
                                $i = 0;
                                while ($bytes >= 1024 && $i < count($units) - 1) {
                                    $bytes /= 1024;
                                    $i++;
                                }
                                $fileSize = round($bytes, 2) . ' ' . $units[$i];
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $dcp->version?->type ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $dcp->version?->audio_lang ?? 'Audio' }} 
                                            @if($dcp->version?->sub_lang)
                                                / ST: {{ $dcp->version->sub_lang }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $fileSize }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $dcp->uploader?->name ?? 'Inconnu' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $dcp->uploaded_at ? $dcp->uploaded_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                @if($dcp->validation_notes)
                                    <span title="{{ $dcp->validation_notes }}">
                                        {{ Str::limit($dcp->validation_notes, 50) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Aucune note</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <p>Aucun DCP uploadé pour ce film</p>
    </div>
@endif
