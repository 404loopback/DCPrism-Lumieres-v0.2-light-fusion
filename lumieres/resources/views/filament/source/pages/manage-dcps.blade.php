<x-filament-panels::page>
    {{-- En-tête avec informations du film --}}
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $record->title }}</h2>
                    <p class="text-gray-600 mt-1">
                        Festival : {{ $record->festival->name ?? 'Non défini' }} ({{ $record->festival->year ?? 'N/A' }})
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        Réalisateur : {{ $record->director ?? 'Non défini' }} | 
                        Durée : {{ $record->duration ?? 'N/A' }} min |
                        Pays : {{ $record->country ?? 'N/A' }}
                    </p>
                </div>
                <div class="flex flex-col items-end">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @switch($record->status)
                            @case('pending') bg-yellow-100 text-yellow-800 @break
                            @case('uploading') bg-blue-100 text-blue-800 @break
                            @case('completed') bg-green-100 text-green-800 @break
                            @default bg-gray-100 text-gray-800
                        @endswitch">
                        {{ ucfirst($record->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Versions attendues et leurs DCPs --}}
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-900">Versions DCP attendues</h3>
        
        @if($this->getExpectedVersions())
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($this->getExpectedVersions() as $expectedVersion)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-medium text-gray-900">
                                    {{ $expectedVersion['label'] }}
                                </h4>
                                @if($expectedVersion['existing_version'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Créée
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        En attente
                                    </span>
                                @endif
                            </div>

                            @if($expectedVersion['existing_version'])
                                {{-- Version existante avec ses DCPs --}}
                                <div class="space-y-3">
                                    <div class="text-sm text-gray-600">
                                        <p><strong>Nomenclature :</strong></p>
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                            {{ $expectedVersion['existing_version']->generated_nomenclature }}
                                        </code>
                                    </div>
                                    
                                    <div class="text-sm text-gray-600">
                                        <p><strong>Paramètres :</strong></p>
                                        <ul class="text-xs space-y-1 mt-1">
                                            <li>Audio: {{ $expectedVersion['existing_version']->audio_lang ?? 'N/A' }}</li>
                                            @if($expectedVersion['existing_version']->sub_lang)
                                                <li>Sous-titres: {{ $expectedVersion['existing_version']->sub_lang }}</li>
                                            @endif
                                            @if($expectedVersion['existing_version']->accessibility)
                                                <li>Accessibilité: {{ $expectedVersion['existing_version']->accessibility }}</li>
                                            @endif
                                        </ul>
                                    </div>

                                    {{-- DCPs de cette version --}}
                                    @php $dcps = $expectedVersion['existing_version']->dcps; @endphp
                                    @if($dcps->count() > 0)
                                        <div class="border-t pt-3">
                                            <p class="text-sm font-medium text-gray-700 mb-2">DCPs uploadés :</p>
                                            @foreach($dcps as $dcp)
                                                <div class="flex items-center justify-between text-xs bg-gray-50 p-2 rounded">
                                                    <span>{{ basename($dcp->file_path) }}</span>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs
                                                        @switch($dcp->status)
                                                            @case('uploaded') bg-blue-100 text-blue-800 @break
                                                            @case('processing') bg-yellow-100 text-yellow-800 @break
                                                            @case('validated') bg-green-100 text-green-800 @break
                                                            @case('failed') bg-red-100 text-red-800 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch">
                                                        {{ ucfirst($dcp->status) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="border-t pt-3">
                                            <p class="text-sm text-orange-600">Aucun DCP uploadé pour cette version</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Version non créée --}}
                                <div class="text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="mt-2 text-sm">Version non encore créée</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Aucune version attendue --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Aucune version attendue</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Aucune version DCP n'a encore été définie pour ce film. Contactez l'équipe du festival pour plus d'informations.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Section upload/actions --}}
    @if($this->getVersionsWithDcps()->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions disponibles</h3>
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Information d'upload</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Utilisez les boutons d'action en haut de page pour :</p>
                            <ul class="list-disc list-inside mt-1">
                                <li>Créer de nouvelles versions si nécessaire</li>
                                <li>Uploader des fichiers DCP pour les versions existantes</li>
                            </ul>
                            <p class="mt-2">
                                <strong>Important :</strong> Vérifiez que vos fichiers DCP correspondent exactement aux paramètres techniques demandés par le festival avant l'upload.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script pour refresh automatique --}}
    <script>
        // Auto-refresh toutes les 30 secondes pour voir les mises à jour de statut
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</x-filament-panels::page>
