@php
    $user = $user ?? $record;
@endphp

<div class="space-y-6">
    {{-- Informations de base --}}
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-3 text-gray-900">Informations personnelles</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Nom :</span>
                <p class="text-sm text-gray-900 mt-1">{{ $user->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Email :</span>
                <p class="text-sm text-gray-900 mt-1">{{ $user->email }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Rôle :</span>
                @if($user->role)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @switch($user->role)
                            @case('admin')
                                bg-red-100 text-red-800
                                @break
                            @case('tech')
                                bg-yellow-100 text-yellow-800
                                @break
                            @case('manager')
                                bg-green-100 text-green-800
                                @break
                            @case('supervisor')
                                bg-blue-100 text-blue-800
                                @break
                            @case('source')
                                bg-purple-100 text-purple-800
                                @break
                            @default
                                bg-gray-100 text-gray-800
                        @endswitch
                    ">
                        @switch($user->role)
                            @case('admin')
                                Administrateur
                                @break
                            @case('tech')
                                Technique
                                @break
                            @case('manager')
                                Manager
                                @break
                            @case('supervisor')
                                Superviseur
                                @break
                            @case('source')
                                Source
                                @break
                            @case('cinema')
                                Cinéma
                                @break
                            @default
                                {{ ucfirst($user->role) }}
                        @endswitch
                    </span>
                @else
                    <span class="text-sm text-gray-400 italic">Aucun rôle assigné</span>
                @endif
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Statut :</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $user->is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800' }}">
                    {{ $user->is_active ? '✓ Actif' : '✗ Inactif' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Festivals assignés --}}
    <div class="bg-blue-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-3 text-gray-900">Festivals assignés</h3>
        @if($user->festivals->count() > 0)
            <div class="space-y-2">
                @foreach($user->festivals as $festival)
                    <div class="flex items-center justify-between bg-white p-3 rounded border">
                        <div>
                            <p class="font-medium text-gray-900">{{ $festival->name }}</p>
                            @if($festival->description)
                                <p class="text-sm text-gray-500">{{ Str::limit($festival->description, 60) }}</p>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400">
                            {{ $festival->created_at->diffForHumans() }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 italic">Aucun festival assigné</p>
        @endif
    </div>

    {{-- Informations de connexion --}}
    <div class="bg-green-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-3 text-gray-900">Informations de connexion</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Email vérifié :</span>
                <div class="mt-1">
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ Vérifié le {{ $user->email_verified_at->format('d/m/Y à H:i') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ✗ Non vérifié
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Dernière connexion :</span>
                <p class="text-sm text-gray-900 mt-1">
                    @if($user->last_login_at)
                        {{ $user->last_login_at->format('d/m/Y à H:i') }}
                        <span class="text-gray-500">({{ $user->last_login_at->diffForHumans() }})</span>
                    @else
                        <span class="text-gray-400 italic">Jamais connecté</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Membre depuis :</span>
                <p class="text-sm text-gray-900 mt-1">
                    {{ $user->created_at->format('d/m/Y à H:i') }}
                    <span class="text-gray-500">({{ $user->created_at->diffForHumans() }})</span>
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Dernière modification :</span>
                <p class="text-sm text-gray-900 mt-1">
                    {{ $user->updated_at->format('d/m/Y à H:i') }}
                    <span class="text-gray-500">({{ $user->updated_at->diffForHumans() }})</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Statistiques d'utilisation (si disponibles) --}}
    @if(method_exists($user, 'movies') || method_exists($user, 'uploads'))
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3 text-gray-900">Statistiques d'activité</h3>
            <div class="grid grid-cols-3 gap-4">
                @if(method_exists($user, 'movies'))
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $user->movies()->count() }}</p>
                        <p class="text-sm text-gray-500">Films liés</p>
                    </div>
                @endif
                @if(method_exists($user, 'uploads'))
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $user->uploads()->count() }}</p>
                        <p class="text-sm text-gray-500">Uploads</p>
                    </div>
                @endif
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $user->festivals->count() }}</p>
                    <p class="text-sm text-gray-500">Festivals</p>
                </div>
            </div>
        </div>
    @endif
</div>
