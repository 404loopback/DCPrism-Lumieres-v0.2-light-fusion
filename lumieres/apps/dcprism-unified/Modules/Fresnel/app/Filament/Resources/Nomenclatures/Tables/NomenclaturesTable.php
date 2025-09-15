<?php

namespace Modules\Fresnel\app\Filament\Resources\Nomenclatures\Tables;

use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Models\Nomenclature;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class NomenclaturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => self::getFestivalNomenclaturesQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Festival')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('nomenclature_preview')
                    ->label('Aperçu de la nomenclature')
                    ->html()
                    ->state(fn ($record) => self::buildNomenclaturePreview($record))
                    ->wrap(),
                Tables\Columns\TextColumn::make('parameters_summary')
                    ->label('Paramètres configurés')
                    ->html()
                    ->state(fn ($record) => self::buildParametersSummary($record)),
                Tables\Columns\TextColumn::make('quality_indicators')
                    ->label('Indicateurs')
                    ->html()
                    ->state(fn ($record) => self::buildQualityIndicators($record)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Dernière MAJ')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('festival_id')
                    ->label('Festival')
                    ->options(Festival::pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('parameter_id')
                    ->label('Paramètre')
                    ->options(Parameter::orderBy('category')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($param) => [
                            $param->id => "{$param->category} - {$param->name}"
                        ]))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Obligation')
                    ->placeholder('Tous')
                    ->trueLabel('Obligatoires')
                    ->falseLabel('Facultatifs'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('preview_nomenclature')
                        ->label('Aperçu détaillé')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn ($record) => 'Aperçu de la nomenclature - ' . $record->name)
                        ->modalDescription('Visualisation complète de la structure de nomenclature pour ce festival')
                        ->modalContent(fn ($record) => view('filament.modals.nomenclature-preview', [
                            'record' => $record,
                            'festival' => $record,
                            'nomenclatures' => $record->nomenclatures
                        ]))
                        ->modalWidth('5xl')
                        ->modalCancelAction(false)
                        ->modalSubmitAction(false),
                    Action::make('test_nomenclature')
                        ->label('Tester avec exemple')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->form([
                            \Filament\Forms\Components\TextInput::make('sample_title')
                                ->label('Titre d\'exemple')
                                ->default('Mon Film Exemple')
                                ->required(),
                            \Filament\Forms\Components\Select::make('sample_format')
                                ->label('Format d\'exemple')
                                ->options([
                                    '2K' => '2K',
                                    '4K' => '4K',
                                    'HD' => 'HD',
                                    'FHD' => 'Full HD'
                                ])
                                ->default('4K'),
                            \Filament\Forms\Components\Select::make('sample_audio')
                                ->label('Audio d\'exemple')
                                ->options([
                                    '5.1' => '5.1',
                                    '7.1' => '7.1', 
                                    'stereo' => 'Stereo'
                                ])
                                ->default('5.1')
                        ])
                        ->action(function ($record, array $data) {
                            $preview = self::generateTestNomenclatureForFestival($record, $data);
                            \Filament\Notifications\Notification::make()
                                ->title('Test de nomenclature')
                                ->body('Résultat: ' . $preview)
                                ->success()
                                ->send();
                        }),
                    Action::make('manage_parameters')
                        ->label('Gérer les paramètres')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('warning')
                        ->url(fn ($record) => route('filament.fresnel.resources.nomenclatures.index', ['tableFilters[festival_id][value]' => $record->id]))
                        ->openUrlInNewTab(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('name');
    }
    
    /**
     * Génère un aperçu de la nomenclature complète pour un festival
     */
    private static function getNomenclaturePreview($record): string
    {
        if (!$record->festival_id) {
            return 'Aucun festival sélectionné';
        }
        
        // Récupérer toutes les nomenclatures du festival ordonnées
        $nomenclatures = Nomenclature::where('festival_id', $record->festival_id)
            ->where('is_active', true)
            ->with('parameter')
            ->orderBy('order_position')
            ->get();
        
        if ($nomenclatures->isEmpty()) {
            return 'Aucune nomenclature configurée';
        }
        
        // Construire un aperçu de la nomenclature
        $parts = [];
        foreach ($nomenclatures as $nomenclature) {
            $paramName = $nomenclature->parameter->name ?? 'PARAM';
            $preview = $nomenclature->getPreview($paramName);
            $parts[] = $preview;
        }
        
        return implode($nomenclatures->first()->separator ?? '_', $parts);
    }
    
    /**
     * Génère un résumé de la configuration d'une nomenclature
     */
    private static function getNomenclatureSummary($record): string
    {
        $summary = [];
        
        // Position dans la nomenclature
        $summary[] = "<span class='text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded'>Position: {$record->order_position}</span>";
        
        // Paramètre
        if ($record->parameter) {
            $paramInfo = $record->parameter->category . ' - ' . $record->parameter->name;
            $summary[] = "<span class='text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded'>{$paramInfo}</span>";
        }
        
        // Séparateur
        if ($record->separator) {
            $summary[] = "<span class='text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded'>Sep: {$record->separator}</span>";
        }
        
        // Préfixe/Suffixe
        if ($record->prefix || $record->suffix) {
            $fix = ($record->prefix ?? '') . '[PARAM]' . ($record->suffix ?? '');
            $summary[] = "<span class='text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded'>{$fix}</span>";
        }
        
        // Valeur par défaut
        if ($record->default_value) {
            $summary[] = "<span class='text-xs bg-green-100 text-green-800 px-2 py-1 rounded'>Défaut: {$record->default_value}</span>";
        }
        
        return '<div class="space-y-1">' . implode('<br>', $summary) . '</div>';
    }
    
    /**
     * Compte le nombre de paramètres configurés pour le festival
     */
    private static function getParametersCount($record): string
    {
        if (!$record->festival_id) {
            return '0';
        }
        
        $total = Nomenclature::where('festival_id', $record->festival_id)->count();
        $active = Nomenclature::where('festival_id', $record->festival_id)
            ->where('is_active', true)
            ->count();
        
        return "{$active}/{$total}";
    }
    
    /**
     * Calcule un score de qualité pour la nomenclature
     */
    private static function getQualityScore($record): string
    {
        $score = 100;
        
        // Pénalités
        if (!$record->is_active) $score -= 30;
        if (!$record->parameter) $score -= 40;
        if ($record->is_required && !$record->default_value) $score -= 20;
        if (empty($record->formatting_rules)) $score -= 10;
        
        $score = max(0, $score);
        return $score . '%';
    }
    
    /**
     * Détermine la couleur du badge de qualité
     */
    private static function getQualityColor($record): string
    {
        $score = (int) str_replace('%', '', self::getQualityScore($record));
        
        return match (true) {
            $score >= 90 => 'success',
            $score >= 70 => 'warning', 
            $score >= 50 => 'danger',
            default => 'gray'
        };
    }
    
    /**
     * Génère un résumé du statut
     */
    private static function getStatusSummary($record): string
    {
        $badges = [];
        
        // Statut actif/inactif
        if ($record->is_active) {
            $badges[] = '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">✓ Actif</span>';
        } else {
            $badges[] = '<span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">✗ Inactif</span>';
        }
        
        // Obligatoire/optionnel
        if ($record->is_required) {
            $badges[] = '<span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">* Requis</span>';
        } else {
            $badges[] = '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Optionnel</span>';
        }
        
        // Règles de formatage
        if (!empty($record->formatting_rules)) {
            $count = count($record->formatting_rules);
            $badges[] = "<span class='text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full'>🔧 {$count} règles</span>";
        }
        
        return '<div class="space-y-1">' . implode('<br>', $badges) . '</div>';
    }
    
    /**
     * Récupère toutes les nomenclatures d'un festival
     */
    private static function getFestivalNomenclatures($festivalId)
    {
        return Nomenclature::where('festival_id', $festivalId)
            ->with(['parameter', 'festival'])
            ->orderBy('order_position')
            ->get();
    }
    
    /**
     * Génère un test de nomenclature avec des données d'exemple
     */
    private static function generateTestNomenclature($record, array $data): string
    {
        // Récupérer toutes les nomenclatures du festival
        $nomenclatures = self::getFestivalNomenclatures($record->festival_id)
            ->where('is_active', true);
            
        if ($nomenclatures->isEmpty()) {
            return 'Aucune nomenclature active pour ce festival';
        }
        
        // Mapper les données d'exemple aux paramètres
        $sampleData = [
            'title' => $data['sample_title'] ?? 'Film Exemple',
            'format' => $data['sample_format'] ?? '4K',
            'audio' => $data['sample_audio'] ?? '5.1',
            'year' => date('Y'),
            'version' => 'VF',
            'festival' => $record->festival->name
        ];
        
        $parts = [];
        foreach ($nomenclatures as $nomenclature) {
            // Trouver une valeur d'exemple pour ce paramètre
            $sampleValue = self::findSampleValueForParameter($nomenclature->parameter, $sampleData);
            $formattedValue = $nomenclature->formatValue($sampleValue);
            
            if ($formattedValue !== null && $formattedValue !== '') {
                $parts[] = $formattedValue;
            }
        }
        
        $separator = $nomenclatures->first()->separator ?? '_';
        return implode($separator, $parts);
    }
    
    /**
     * Trouve une valeur d'exemple appropriée pour un paramètre
     */
    private static function findSampleValueForParameter($parameter, array $sampleData): string
    {
        if (!$parameter) {
            return 'PARAM';
        }
        
        // Mapping basé sur le nom ou la catégorie du paramètre
        $paramName = strtolower($parameter->name);
        $paramCategory = strtolower($parameter->category ?? '');
        
        return match (true) {
            str_contains($paramName, 'titre') || str_contains($paramName, 'title') => $sampleData['title'],
            str_contains($paramName, 'format') || str_contains($paramName, 'resolution') => $sampleData['format'],
            str_contains($paramName, 'audio') || str_contains($paramName, 'son') => $sampleData['audio'],
            str_contains($paramName, 'annee') || str_contains($paramName, 'year') => $sampleData['year'],
            str_contains($paramName, 'version') || str_contains($paramName, 'langue') => $sampleData['version'],
            str_contains($paramName, 'festival') => $sampleData['festival'],
            str_contains($paramCategory, 'video') => $sampleData['format'],
            str_contains($paramCategory, 'audio') => $sampleData['audio'],
            str_contains($paramCategory, 'contenu') => $sampleData['title'],
            default => strtoupper($parameter->name)
        };
    }
    
    /**
     * Requête simple pour récupérer les festivals avec nomenclatures
     */
    private static function getFestivalNomenclaturesQuery()
    {
        return Festival::query()
            ->whereHas('nomenclatures')
            ->with(['nomenclatures.parameter'])
            ->withCount('nomenclatures')
            ->orderBy('name');
    }
    
    /**
     * Construit l'aperçu de nomenclature pour un festival
     */
    private static function buildNomenclaturePreview($festival): string
    {
        $nomenclatures = $festival->nomenclatures
            ->where('is_active', true)
            ->sortBy('order_position');
            
        if ($nomenclatures->isEmpty()) {
            return '<div class="text-gray-500 italic text-xs">Aucune nomenclature active</div>';
        }
        
        $parts = [];
        foreach ($nomenclatures as $nomenclature) {
            $paramName = $nomenclature->parameter->name ?? 'PARAM';
            $sample = match(strtolower($paramName)) {
                'titre', 'title' => 'FILM_EXEMPLE',
                'format', 'resolution' => '4K', 
                'audio', 'son' => '51',
                'annee', 'year' => date('Y'),
                'version' => 'VF',
                default => strtoupper($paramName)
            };
            
            $formatted = $nomenclature->formatValue($sample);
            if ($formatted) {
                // Mettre chaque paramètre entre crochets
                $parts[] = "[<span class='text-blue-600 font-medium'>{$formatted}</span>]";
            }
        }
        
        $separator = $nomenclatures->first()->separator ?? '_';
        $preview = implode("<span class='text-gray-400'>{$separator}</span>", $parts);
        
        return "<div class='font-mono text-xs bg-gray-50 px-2 py-1 rounded border'>{$preview}</div>";
    }
    
    /**
     * Construit le résumé des paramètres pour un festival
     */
    private static function buildParametersSummary($festival): string
    {
        $nomenclatures = $festival->nomenclatures;
        $total = $nomenclatures->count();
        $active = $nomenclatures->where('is_active', true)->count();
        $required = $nomenclatures->where('is_required', true)->count();
        
        $badges = [
            "<span class='text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full'>Total: {$total}</span>",
            "<span class='text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full'>Actifs: {$active}</span>",
            "<span class='text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full'>Requis: {$required}</span>"
        ];
        
        return '<div class="space-y-1">' . implode('<br>', $badges) . '</div>';
    }
    
    /**
     * Construit les indicateurs de qualité pour un festival
     */
    private static function buildQualityIndicators($festival): string
    {
        $nomenclatures = $festival->nomenclatures;
        
        if ($nomenclatures->isEmpty()) {
            return '<span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">Aucune config</span>';
        }
        
        $activeCount = $nomenclatures->where('is_active', true)->count();
        $totalCount = $nomenclatures->count();
        $qualityRatio = $totalCount > 0 ? ($activeCount / $totalCount) : 0;
        
        $missingRequired = $nomenclatures
            ->where('is_required', true)
            ->where('default_value', null)
            ->count();
            
        $withRules = $nomenclatures
            ->filter(fn($n) => !empty($n->formatting_rules))
            ->count();
        
        // Score global
        $score = 100;
        if ($qualityRatio < 0.8) $score -= 20;  // Pas assez d'actifs
        if ($missingRequired > 0) $score -= 15; // Paramètres requis sans défaut
        if ($withRules / $totalCount < 0.5) $score -= 10; // Peu de règles
        
        $score = max(0, $score);
        
        $badge = match (true) {
            $score >= 90 => '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">✓ Excellent</span>',
            $score >= 70 => '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">⚠ Bon</span>',
            default => '<span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">✗ À améliorer</span>'
        };
        
        $details = [];
        if ($missingRequired > 0) {
            $details[] = "<span class='text-xs bg-red-100 text-red-800 px-1 py-0.5 rounded'>{$missingRequired} manquants</span>";
        }
        if ($withRules > 0) {
            $details[] = "<span class='text-xs bg-purple-100 text-purple-800 px-1 py-0.5 rounded'>{$withRules} règles</span>";
        }
        
        $detailsHtml = !empty($details) ? '<br>' . implode(' ', $details) : '';
        
        return "<div class='flex flex-col gap-1'>{$badge}{$detailsHtml}</div>";
    }
    
    /**
     * Génère un test de nomenclature pour un festival
     */
    private static function generateTestNomenclatureForFestival($festival, array $data): string
    {
        $nomenclatures = $festival->nomenclatures
            ->where('is_active', true)
            ->sortBy('order_position');
            
        if ($nomenclatures->isEmpty()) {
            return 'Aucune nomenclature active pour ce festival';
        }
        
        // Mapper les données d'exemple aux paramètres
        $sampleData = [
            'title' => $data['sample_title'] ?? 'Film Exemple',
            'format' => $data['sample_format'] ?? '4K',
            'audio' => $data['sample_audio'] ?? '5.1',
            'year' => date('Y'),
            'version' => 'VF',
            'festival' => $festival->name
        ];
        
        $parts = [];
        foreach ($nomenclatures as $nomenclature) {
            // Trouver une valeur d'exemple pour ce paramètre
            $sampleValue = self::findSampleValueForParameter($nomenclature->parameter, $sampleData);
            $formattedValue = $nomenclature->formatValue($sampleValue);
            
            if ($formattedValue !== null && $formattedValue !== '') {
                $parts[] = $formattedValue;
            }
        }
        
        $separator = $nomenclatures->first()->separator ?? '_';
        return implode($separator, $parts);
    }
}
