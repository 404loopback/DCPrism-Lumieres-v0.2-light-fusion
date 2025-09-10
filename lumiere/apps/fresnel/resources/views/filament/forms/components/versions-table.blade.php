@php
use App\Models\Version;
@endphp

<div class="fi-fo-field-wrp">
    @if ($versions->isEmpty())
        <div class="fi-placeholder">
            <div class="fi-placeholder-content">
                <div class="fi-placeholder-icon-wrp">
                    <svg class="fi-placeholder-icon" style="width: 3rem; height: 3rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h4 class="fi-placeholder-title">Aucune version générée</h4>
                <p class="fi-placeholder-description">
                    Les versions seront générées automatiquement lors de la création du film selon les nomenclatures configurées pour ce festival.
                </p>
            </div>
        </div>
    @else
        <div class="fi-ta-wrp">
            <div class="fi-ta">
                <div class="fi-ta-header-ctn">
                    <div class="fi-ta-header">
                        <h3 class="fi-ta-header-heading">
                            Versions générées ({{ $versions->count() }})
                        </h3>
                        <p class="fi-ta-header-description">
                            Liste des versions créées pour ce film avec leurs caractéristiques détaillées.
                        </p>
                    </div>
                </div>
                
                <div class="fi-ta-content">
                    <div class="fi-ta-table-wrp">
                        <table class="fi-ta-table">
                            <thead class="fi-ta-table-header">
                                <tr class="fi-ta-table-row">
                                    <th class="fi-ta-table-cell-header">Type</th>
                                    <th class="fi-ta-table-cell-header">Format</th>
                                    <th class="fi-ta-table-cell-header">Audio</th>
                                    <th class="fi-ta-table-cell-header">Sous-titres</th>
                                    <th class="fi-ta-table-cell-header">Accessibilité</th>
                                    <th class="fi-ta-table-cell-header">Nomenclature</th>
                                    <th class="fi-ta-table-cell-header">Créé le</th>
                                </tr>
                            </thead>
                            <tbody class="fi-ta-table-body">
                                @foreach ($versions as $version)
                                    <tr class="fi-ta-table-row hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="fi-ta-table-cell">
                                            <div class="flex items-center gap-3">
                                                <div class="flex flex-col">
                                                    <span class="fi-badge fi-badge-size-sm fi-color-primary">
                                                        {{ $version->type }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ Version::TYPES[$version->type] ?? $version->type }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-table-cell">
                                            @if($version->format)
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ $version->format }}</span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ Version::FORMATS[$version->format] ?? $version->format }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="fi-ta-table-cell">
                                            @if($version->audio_lang)
                                                <div class="flex items-center gap-2">
                                                    <span class="fi-badge fi-badge-size-sm fi-color-gray">
                                                        {{ strtoupper($version->audio_lang) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="fi-ta-table-cell">
                                            @if($version->sub_lang)
                                                <div class="flex items-center gap-2">
                                                    <span class="fi-badge fi-badge-size-sm fi-color-gray">
                                                        {{ strtoupper($version->sub_lang) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="fi-ta-table-cell">
                                            @if($version->accessibility)
                                                <span class="fi-badge fi-badge-size-sm fi-color-warning">
                                                    {{ $version->accessibility }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="fi-ta-table-cell">
                                            @if($version->generated_nomenclature)
                                                <div class="flex flex-col">
                                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                                        {{ $version->generated_nomenclature }}
                                                    </code>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="fi-ta-table-cell">
                                            <div class="flex flex-col">
                                                <span class="text-sm">{{ $version->created_at->format('d/m/Y') }}</span>
                                                <span class="text-xs text-gray-500">{{ $version->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.fi-ta-wrp {
    @apply overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10;
}

.fi-ta {
    @apply divide-y divide-gray-200 dark:divide-white/10;
}

.fi-ta-header-ctn {
    @apply px-6 py-4;
}

.fi-ta-header-heading {
    @apply text-base font-semibold leading-6 text-gray-950 dark:text-white;
}

.fi-ta-header-description {
    @apply mt-1 text-sm text-gray-500 dark:text-gray-400;
}

.fi-ta-content {
    @apply overflow-x-auto;
}

.fi-ta-table-wrp {
    @apply min-w-full;
}

.fi-ta-table {
    @apply min-w-full divide-y divide-gray-200 dark:divide-white/10;
}

.fi-ta-table-header {
    @apply bg-gray-50 dark:bg-white/5;
}

.fi-ta-table-cell-header {
    @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400;
}

.fi-ta-table-cell {
    @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white;
}

.fi-badge {
    @apply inline-flex items-center rounded-full font-medium ring-1 ring-inset;
}

.fi-badge-size-sm {
    @apply px-2 py-1 text-xs;
}

.fi-color-primary {
    @apply bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30;
}

.fi-color-gray {
    @apply bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20;
}

.fi-color-warning {
    @apply bg-warning-50 text-warning-800 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20;
}

.fi-placeholder {
    @apply flex flex-col items-center justify-center px-6 py-12;
}

.fi-placeholder-icon-wrp {
    @apply mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-800;
}

.fi-placeholder-icon {
    @apply text-gray-500 dark:text-gray-400;
}

.fi-placeholder-title {
    @apply text-sm font-medium text-gray-900 dark:text-white;
}

.fi-placeholder-description {
    @apply mt-1 text-sm text-gray-500 dark:text-gray-400;
}
</style>
