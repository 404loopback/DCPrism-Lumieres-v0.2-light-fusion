@php
    $record = $record ?? null;
@endphp

<div class="space-y-6">
    <!-- Informations générales -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-semibold mb-3">Informations générales</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-600">Type de validation:</span>
                <span class="ml-2">{{ ucfirst($record->validation_type) }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Règle:</span>
                <span class="ml-2">{{ $record->rule_name }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Statut:</span>
                <span class="ml-2 px-2 py-1 rounded-full text-xs 
                    @if($record->status === 'passed') bg-green-100 text-green-800
                    @elseif($record->status === 'failed') bg-red-100 text-red-800
                    @elseif($record->status === 'warning') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($record->status) }}
                </span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Sévérité:</span>
                <span class="ml-2 px-2 py-1 rounded-full text-xs
                    @if($record->severity === 'error') bg-red-100 text-red-800
                    @elseif($record->severity === 'warning') bg-yellow-100 text-yellow-800
                    @else bg-blue-100 text-blue-800 @endif">
                    {{ ucfirst($record->severity) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($record->description)
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-800 mb-2">Description</h4>
        <p class="text-gray-700 whitespace-pre-wrap">{{ $record->description }}</p>
    </div>
    @endif

    <!-- Valeurs attendue vs réelle -->
    @if($record->expected_value || $record->actual_value)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($record->expected_value)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h4 class="font-semibold text-green-800 mb-2">Valeur attendue</h4>
            <p class="text-green-700 font-mono text-sm whitespace-pre-wrap">{{ $record->expected_value }}</p>
        </div>
        @endif
        
        @if($record->actual_value)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h4 class="font-semibold text-red-800 mb-2">Valeur réelle</h4>
            <p class="text-red-700 font-mono text-sm whitespace-pre-wrap">{{ $record->actual_value }}</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Suggestion de correction -->
    @if($record->suggestion)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="font-semibold text-blue-800 mb-2">
            Suggestion de correction
            @if($record->can_auto_fix)
                <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                    Auto-correction possible
                </span>
            @endif
        </h4>
        <p class="text-blue-700 whitespace-pre-wrap">{{ $record->suggestion }}</p>
    </div>
    @endif

    <!-- Détails techniques -->
    @if($record->details && count($record->details ?? []) > 0)
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-800 mb-2">Détails techniques</h4>
        <pre class="text-xs text-gray-600 whitespace-pre-wrap overflow-x-auto">{{ json_encode($record->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

    <!-- Informations de validation -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="font-semibold text-gray-800 mb-3">Informations de validation</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-600">Validé le:</span>
                <div class="mt-1">{{ $record->validated_at?->format('d/m/Y H:i:s') ?? 'Non défini' }}</div>
            </div>
            @if($record->validator_version)
            <div>
                <span class="font-medium text-gray-600">Version du validateur:</span>
                <div class="mt-1 font-mono text-xs">{{ $record->validator_version }}</div>
            </div>
            @endif
            <div>
                <span class="font-medium text-gray-600">Créé le:</span>
                <div class="mt-1">{{ $record->created_at?->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
    </div>
</div>
