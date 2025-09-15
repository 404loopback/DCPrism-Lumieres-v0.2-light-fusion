<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-medium text-blue-900 mb-2">💡 Guide de Configuration</h3>
            <div class="text-sm text-blue-700 space-y-1">
                <p><strong>Objectif :</strong> Définir comment les noms de vos films seront générés automatiquement</p>
                <p><strong>Exemple :</strong> Si vous configurez [TITRE]_[RESOLUTION]_[LANGUE], vous obtiendrez : MonFilm_2K_FR.dcp</p>
                <p><strong>Ordre :</strong> Glissez-déposez les paramètres pour définir leur ordre d'apparition</p>
            </div>
        </div>

        {{ $this->form }}

        <div class="flex justify-end">
            {{ $this->saveAction }}
        </div>
    </div>

    <script>
        // Rafraîchir l'aperçu quand on réordonne
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Déclencher un événement personnalisé pour mettre à jour l'aperçu
                        if (window.Livewire) {
                            window.Livewire.dispatch('refreshPreview');
                        }
                    }
                });
            });

            const repeater = document.querySelector('[data-field-wrapper="parameters"]');
            if (repeater) {
                observer.observe(repeater, {
                    childList: true,
                    subtree: true
                });
            }
        });
    </script>
</x-filament-panels::page>
