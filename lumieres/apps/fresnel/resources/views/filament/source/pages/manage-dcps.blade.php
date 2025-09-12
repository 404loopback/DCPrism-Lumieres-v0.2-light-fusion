<x-filament-panels::page>
    <div class="grid gap-6">
        
        {{-- Informations du film --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="fi-section-header flex items-center gap-3 mb-4">
                    <div class="fi-section-header-icon flex h-8 w-8 items-center justify-center rounded-lg bg-primary-500/10">
                        <x-heroicon-o-film class="h-5 w-5 text-primary-500" />
                    </div>
                    <div>
                        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            {{ $this->record->title }}
                        </h3>
                        <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                            Film demandé par {{ $this->record->festivals->count() }} festival(s)
                        </p>
                    </div>
                </div>
                
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @if($this->record->duration)
                        <div class="text-center p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Durée</div>
                            <div class="font-semibold">{{ $this->record->duration }} min</div>
                        </div>
                    @endif
                    
                    @if($this->record->format)
                        <div class="text-center p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Format</div>
                            <div class="font-semibold">{{ $this->record->format }}</div>
                        </div>
                    @endif
                    
                    <div class="text-center p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Statut</div>
                        <div class="font-semibold">
                            @php
                                $statuses = \App\Models\Movie::getStatuses();
                                $statusColors = [
                                    'pending' => 'text-gray-600',
                                    'token_sent' => 'text-yellow-600',
                                    'uploading' => 'text-blue-600',
                                    'upload_ok' => 'text-green-600',
                                    'validated' => 'text-green-700',
                                    'rejected' => 'text-red-600',
                                ];
                            @endphp
                            <span class="{{ $statusColors[$this->record->status] ?? 'text-gray-600' }}">
                                {{ $statuses[$this->record->status] ?? $this->record->status }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="text-center p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Versions Créées</div>
                        <div class="font-semibold">{{ $this->record->versions->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Versions attendues vs créées --}}
        @php
            $expectedVersions = $this->getExpectedVersions();
            $versionsWithDcps = $this->getVersionsWithDcps();
        @endphp
        
        @if(count($expectedVersions) > 0)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-content p-6">
                    <div class="fi-section-header flex items-center gap-3 mb-6">
                        <div class="fi-section-header-icon flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/10">
                            <x-heroicon-o-check-badge class="h-5 w-5 text-green-500" />
                        </div>
                        <div>
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Versions Demandées ({{ count($expectedVersions) }})
                            </h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                                État d'avancement des versions DCP à fournir
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($expectedVersions as $expected)
                            @php
                                $existingVersion = $expected['existing_version'];
                                $hasDcp = $existingVersion ? $existingVersion->dcps->count() > 0 : false;
                            @endphp
                            
                            <div class="border rounded-lg p-4 {{ $hasDcp ? 'border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20' : ($existingVersion ? 'border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800') }}">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold">{{ $expected['label'] }}</h4>
                                    @if($hasDcp)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-100">
                                            <x-heroicon-m-check class="h-3 w-3 mr-1" />
                                            DCP Uploadé
                                        </span>
                                    @elseif($existingVersion)
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100">
                                            <x-heroicon-m-clock class="h-3 w-3 mr-1" />
                                            Version Créée
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-900 dark:text-gray-100">
                                            <x-heroicon-m-x-mark class="h-3 w-3 mr-1" />
                                            À Créer
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>{{ $expected['type'] }}</strong>
                                    @if($existingVersion)
                                        <div class="mt-1">
                                            Audio: {{ $existingVersion->audioLanguage?->name ?? 'Non défini' }}
                                            @if($existingVersion->sub_lang)
                                                <br>Sous-titres: {{ $existingVersion->subtitleLanguage?->name ?? $existingVersion->sub_lang }}
                                            @endif
                                        </div>
                                        
                                        @if($existingVersion->dcps->count() > 0)
                                            <div class="mt-2 text-xs">
                                                <strong>{{ $existingVersion->dcps->count() }} DCP(s) :</strong>
                                                @foreach($existingVersion->dcps as $dcp)
                                                    <div class="flex justify-between items-center mt-1">
                                                        <span>{{ \App\Models\Dcp::STATUSES[$dcp->status] ?? $dcp->status }}</span>
                                                        @if($dcp->file_size)
                                                            <span class="text-gray-500">{{ $dcp->formatted_file_size }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-content p-6">
                    <div class="text-center py-12">
                        <x-heroicon-o-exclamation-triangle class="mx-auto h-12 w-12 text-orange-500 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Aucune Version Spécifiée
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Le festival n'a pas encore spécifié les versions DCP attendues pour ce film.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Versions existantes détaillées --}}
        @if($versionsWithDcps->count() > 0)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-content p-6">
                    <div class="fi-section-header flex items-center gap-3 mb-6">
                        <div class="fi-section-header-icon flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10">
                            <x-heroicon-o-archive-box class="h-5 w-5 text-blue-500" />
                        </div>
                        <div>
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Versions Créées ({{ $versionsWithDcps->count() }})
                            </h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                                Détail des versions et DCPs uploadés
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($versionsWithDcps as $version)
                            <div class="border rounded-lg p-4 dark:border-gray-700">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-semibold">{{ $version->generated_nomenclature }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ \App\Models\Version::TYPES[$version->type] ?? $version->type }}
                                            • Audio: {{ $version->audioLanguage?->name ?? 'Non défini' }}
                                            @if($version->sub_lang)
                                                • ST: {{ $version->subtitleLanguage?->name ?? $version->sub_lang }}
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">
                                            Créée le {{ $version->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                                
                                @if($version->dcps->count() > 0)
                                    <div class="border-t pt-3 dark:border-gray-600">
                                        <h5 class="font-medium text-sm mb-2">DCPs ({{ $version->dcps->count() }}) :</h5>
                                        <div class="space-y-2">
                                            @foreach($version->dcps as $dcp)
                                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded dark:bg-gray-800">
                                                    <div>
                                                        <span class="font-medium text-sm">
                                                            {{ \App\Models\Dcp::STATUSES[$dcp->status] ?? $dcp->status }}
                                                        </span>
                                                        @if($dcp->validation_notes)
                                                            <div class="text-xs text-gray-500 mt-1">{{ $dcp->validation_notes }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right text-sm">
                                                        @if($dcp->file_size)
                                                            <div>{{ $dcp->formatted_file_size }}</div>
                                                        @endif
                                                        <div class="text-gray-500">
                                                            {{ $dcp->uploaded_at?->format('d/m/Y H:i') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="border-t pt-3 text-center text-gray-500 dark:border-gray-600">
                                        <p class="text-sm">Aucun DCP uploadé pour cette version</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Instructions --}}
        <div class="fi-section rounded-xl bg-blue-50 border border-blue-200 dark:bg-blue-950/20 dark:border-blue-800">
            <div class="fi-section-content p-6">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-information-circle class="h-6 w-6 text-blue-500 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">
                            Instructions d'Upload
                        </h3>
                        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                            <li>• <strong>Créez d'abord une version</strong> pour chaque type demandé (VO, VOST, VF, etc.)</li>
                            <li>• <strong>Uploadez ensuite les DCPs</strong> correspondant à chaque version</li>
                            <li>• <strong>Formats acceptés :</strong> ZIP, TAR (taille max : 50GB)</li>
                            <li>• <strong>Upload sécurisé :</strong> Vos fichiers sont stockés sur Backblaze B2</li>
                            <li>• <strong>Traitement automatique :</strong> Une analyse sera effectuée après chaque upload</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Actions modals --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
