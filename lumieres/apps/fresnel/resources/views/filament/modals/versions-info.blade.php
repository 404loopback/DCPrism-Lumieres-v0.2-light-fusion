<x-filament::section icon="heroicon-o-information-circle" icon-color="info">
    <x-slot name="heading">À propos des versions</x-slot>
    
    <p>Les versions représentent les différentes variantes linguistiques d'un film : version originale (VO), version française (VF), version sous-titrée (VOST), etc.</p>
</x-filament::section>

<x-filament::section>
    <x-slot name="heading">Types de versions disponibles</x-slot>
    
    <div class="space-y-3">
        <div class="flex items-center space-x-3">
            <x-filament::badge color="info">VO</x-filament::badge>
            <span class="text-sm">Version Originale</span>
        </div>
        
        <div class="flex items-center space-x-3">
            <x-filament::badge color="success">VF</x-filament::badge>
            <span class="text-sm">Version Française</span>
        </div>
        
        <div class="flex items-center space-x-3">
            <x-filament::badge color="primary">VOST</x-filament::badge>
            <span class="text-sm">VO Sous-titrée</span>
        </div>
        
        <div class="flex items-center space-x-3">
            <x-filament::badge color="warning">VOSTF</x-filament::badge>
            <span class="text-sm">VO Sous-titrée Français</span>
        </div>
        
        <div class="flex items-center space-x-3">
            <x-filament::badge color="gray">DUB</x-filament::badge>
            <span class="text-sm">Doublage</span>
        </div>
    </div>
</x-filament::section>

<x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="warning">
    <x-slot name="heading">Actions disponibles</x-slot>
    
    <p>Vous pouvez générer des nomenclatures automatiques pour les versions et prévisualiser les résultats avant application. Les versions sont automatiquement créées lors de l'ajout de films au festival.</p>
</x-filament::section>
