<div>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🎛️</span>
                <span>Aperçu Interactif de la Nomenclature</span>
            </div>
        </x-slot>
        
        <x-slot name="description">
            Glissez-déposez les tags pour réorganiser l'ordre de la nomenclature en temps réel
        </x-slot>
        
        @php
            $page = app()->make(\App\Filament\Manager\Resources\NomenclatureResource\Pages\ListNomenclatures::class);
            $previewData = $page->getNomenclaturePreviewData();
        @endphp
        
        @if(isset($previewData['error']))
            <div class="p-8 text-center">
                <div class="text-6xl mb-4">{docker exec dcprism-app sh -c cat
    {{-- Scripts pour le drag & drop avec SortableJS --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortableContainer = document.getElementById('sortable-tags');
            
            // Initialiser SortableJS
            new Sortable(sortableContainer, {
                animation: 200,
                ghostClass: 'tag-ghost',
                chosenClass: 'tag-chosen',
                dragClass: 'tag-drag',
                handle: '.drag-handle',
                
                onStart: function(evt) {
                    document.body.classList.add('dragging');
                },
                
                onEnd: function(evt) {
                    document.body.classList.remove('dragging');
                    updateNomenclatureResult();
                    updatePositionNumbers();
                }
            });
            
            function updateNomenclatureResult() {
                const activeTags = sortableContainer.querySelectorAll('.nomenclature-tag[data-active="1"]');
                const parts = [];
                let separator = '_';
                
                activeTags.forEach((tag, index) => {
                    const sample = tag.dataset.sample || 'PARAM';
                    const prefix = tag.dataset.prefix || '';
                    const suffix = tag.dataset.suffix || '';
                    const tagSeparator = tag.dataset.separator || '_';
                    
                    if (index === 0) {
                        separator = tagSeparator;
                    }
                    
                    const formatted = prefix + sample + suffix;
                    if (formatted) {
                        parts.push(formatted);
                    }
                });
                
                const result = parts.join(separator);
                const resultElement = document.getElementById('nomenclature-result');
                const lengthElement = document.getElementById('result-length');
                
                if (resultElement) {
                    resultElement.textContent = result || 'Aucun paramètre actif';
                }
                
                if (lengthElement) {
                    lengthElement.textContent = result.length + ' caractères';
                }
            }
            
            function updatePositionNumbers() {
                const tags = sortableContainer.querySelectorAll('.nomenclature-tag');
                tags.forEach((tag, index) => {
                    const positionNumber = tag.querySelector('.position-number');
                    if (positionNumber) {
                        positionNumber.textContent = '#' + (index + 1);
                    }
                });
            }
            
            updateNomenclatureResult();
        });
    </script>
    @endpush
    
    <style>
        .nomenclature-tag {
            transition: all 0.2s ease;
        }
        
        .nomenclature-tag.inactive {
            opacity: 0.7;
        }
        
        .tag-ghost {
            opacity: 0.4;
            transform: scale(0.95);
        }
        
        .tag-chosen {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        
        .tag-drag {
            transform: rotate(5deg);
        }
        
        .drag-handle {
            cursor: grab;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        body.dragging {
            cursor: grabbing;
        }
        
        body.dragging * {
            pointer-events: none;
        }
        
        body.dragging .nomenclature-tag {
            pointer-events: all;
        }
        
        #nomenclature-result {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .tag-content {
                flex-wrap: wrap;
                gap: 0.25rem;
            }
        }
    </style>
</x-filament-widgets::widget>
