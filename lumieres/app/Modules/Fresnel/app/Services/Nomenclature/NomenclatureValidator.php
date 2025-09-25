<?php

namespace Modules\Fresnel\app\Services\Nomenclature;

use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Nomenclature;

/**
 * Centralized validator for nomenclature operations
 * Extracted from UnifiedNomenclatureService for better separation of concerns
 */
class NomenclatureValidator
{
    /**
     * Valider la configuration d'une nomenclature
     */
    public function validateNomenclatureConfig(Nomenclature $nomenclature): array
    {
        $errors = [];
        $warnings = [];

        // Vérifier que le paramètre existe et est actif
        $parameter = $nomenclature->resolveParameter();
        if (!$parameter || !$parameter->is_active) {
            $errors[] = "Le paramètre associé n'existe pas ou n'est pas actif";
        }

        // Vérifier que la position est unique pour le festival
        $duplicate = Nomenclature::where('festival_id', $nomenclature->festival_id)
            ->where('order_position', $nomenclature->order_position)
            ->where('id', '!=', $nomenclature->id)
            ->exists();

        if ($duplicate) {
            $errors[] = "La position {$nomenclature->order_position} est déjà utilisée pour ce festival";
        }

        // Valider les règles de formatage JSON
        if (!empty($nomenclature->formatting_rules) && !is_array($nomenclature->formatting_rules)) {
            $errors[] = 'Les règles de formatage doivent être un tableau valide';
        }

        // Valider les règles conditionnelles
        if (!empty($nomenclature->conditional_rules)) {
            $conditionErrors = $this->validateConditionalRules($nomenclature->conditional_rules);
            $errors = array_merge($errors, $conditionErrors);
        }

        // Valider les règles de formatage
        if (!empty($nomenclature->formatting_rules)) {
            $formatErrors = $this->validateFormattingRules($nomenclature->formatting_rules);
            $errors = array_merge($errors, $formatErrors);
        }

        // Vérifier la longueur des préfixes/suffixes
        if (!empty($nomenclature->prefix) && strlen($nomenclature->prefix) > 50) {
            $warnings[] = 'Le préfixe est très long (max recommandé: 50 caractères)';
        }

        if (!empty($nomenclature->suffix) && strlen($nomenclature->suffix) > 50) {
            $warnings[] = 'Le suffixe est très long (max recommandé: 50 caractères)';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Valider la conformité d'une nomenclature générée
     */
    public function validateGeneratedNomenclature(string $nomenclature, Festival $festival): array
    {
        $issues = [];
        $suggestions = [];

        // Validation de la longueur
        if (strlen($nomenclature) > 255) {
            $issues[] = 'Nomenclature trop longue (max 255 caractères)';
        }

        if (strlen($nomenclature) < 3) {
            $issues[] = 'Nomenclature trop courte (min 3 caractères)';
        }

        // Validation des caractères autorisés
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $nomenclature)) {
            $issues[] = 'Caractères non autorisés détectés';
            $suggestions[] = 'Utiliser uniquement: lettres, chiffres, _, -, .';
        }

        // Validation des parties obligatoires
        $parts = $this->parseNomenclatureParts($nomenclature);
        $requiredRules = $festival->nomenclatures()->where('is_active', true)->where('is_required', true)->get();

        if (count($parts) < $requiredRules->count()) {
            $issues[] = "Nombre insuffisant de parties (requis: {$requiredRules->count()}, trouvé: ".count($parts).')';
        }

        // Suggestions d'amélioration
        if (count($parts) > 7) {
            $suggestions[] = 'Considérer simplifier la nomenclature (plus de 7 parties peut être complexe)';
        }

        if (strlen($nomenclature) > 100) {
            $suggestions[] = 'Nomenclature longue, considérer raccourcir certaines parties';
        }

        // Vérifier la cohérence des séparateurs
        if (!$this->hasConsistentSeparators($nomenclature)) {
            $warnings[] = 'Séparateurs incohérents détectés';
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'suggestions' => $suggestions,
            'warnings' => $warnings ?? [],
            'analyzed_parts' => $parts,
            'score' => $this->calculateNomenclatureScore($nomenclature, $issues, $suggestions),
        ];
    }

    /**
     * Valider la configuration d'un festival complet
     */
    public function validateFestivalNomenclatureConfig(Festival $festival): array
    {
        $nomenclatures = $festival->nomenclatures()->where('is_active', true)->ordered()->get();
        $errors = [];
        $warnings = [];

        if ($nomenclatures->isEmpty()) {
            $errors[] = 'Aucune nomenclature active configurée pour ce festival';
            return ['is_valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Vérifier l'unicité des positions
        $positions = $nomenclatures->pluck('order_position')->toArray();
        if (count($positions) !== count(array_unique($positions))) {
            $errors[] = 'Positions dupliquées détectées dans la configuration';
        }

        // Vérifier la continuité des positions (1, 2, 3... sans trous)
        $expectedPositions = range(1, count($positions));
        sort($positions);
        if ($positions !== $expectedPositions) {
            $warnings[] = 'Les positions ne sont pas continues (recommandé: 1, 2, 3...)';
        }

        // Valider chaque nomenclature individuellement
        foreach ($nomenclatures as $nomenclature) {
            $validation = $this->validateNomenclatureConfig($nomenclature);
            if (!$validation['is_valid']) {
                $errors[] = "Nomenclature position {$nomenclature->order_position}: " . implode(', ', $validation['errors']);
            }
            if (!empty($validation['warnings'])) {
                $warnings = array_merge($warnings, $validation['warnings']);
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'nomenclature_count' => $nomenclatures->count(),
            'required_count' => $nomenclatures->where('is_required', true)->count(),
        ];
    }

    /**
     * Valider les paramètres de configuration avant création
     */
    public function validateParameterConfigs(array $parameterConfigs): array
    {
        $errors = [];
        $positions = [];

        foreach ($parameterConfigs as $index => $config) {
            $configErrors = $this->validateSingleParameterConfig($config, $index);
            $errors = array_merge($errors, $configErrors);

            // Vérifier l'unicité des positions
            if (isset($config['order_position'])) {
                if (in_array($config['order_position'], $positions)) {
                    $errors[] = "Configuration {$index}: Position {$config['order_position']} utilisée plusieurs fois";
                }
                $positions[] = $config['order_position'];
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validation spécialisées - méthodes privées
     */

    private function validateConditionalRules(array $conditionalRules): array
    {
        $errors = [];

        if (!is_array($conditionalRules)) {
            return ['Les règles conditionnelles doivent être un tableau'];
        }

        foreach ($conditionalRules as $index => $rule) {
            if (!is_array($rule)) {
                $errors[] = "Règle conditionnelle {$index}: doit être un tableau";
                continue;
            }

            if (!isset($rule['if']) || !is_array($rule['if'])) {
                $errors[] = "Règle conditionnelle {$index}: 'if' manquant ou invalide";
            }

            if (!isset($rule['then'])) {
                $errors[] = "Règle conditionnelle {$index}: 'then' manquant";
            }

            // Valider la structure de la condition 'if'
            if (isset($rule['if'])) {
                $conditionErrors = $this->validateConditionStructure($rule['if'], $index);
                $errors = array_merge($errors, $conditionErrors);
            }
        }

        return $errors;
    }

    private function validateFormattingRules(array $formattingRules): array
    {
        $errors = [];
        $validRules = [
            'uppercase', 'lowercase', 'capitalize', 'trim', 
            'max_length', 'replace', 'regex', 'pad_left', 'pad_right'
        ];

        foreach ($formattingRules as $rule => $config) {
            if (!in_array($rule, $validRules)) {
                $errors[] = "Règle de formatage inconnue: {$rule}";
                continue;
            }

            // Validation spécifique par type de règle
            switch ($rule) {
                case 'max_length':
                case 'pad_left':
                case 'pad_right':
                    if (!is_int($config) && !isset($config['length'])) {
                        $errors[] = "Règle {$rule}: longueur requise";
                    }
                    break;

                case 'replace':
                    if (!isset($config['search']) || !isset($config['replace'])) {
                        $errors[] = "Règle replace: 'search' et 'replace' requis";
                    }
                    break;

                case 'regex':
                    if (!isset($config['pattern'])) {
                        $errors[] = "Règle regex: 'pattern' requis";
                    } elseif (@preg_match($config['pattern'], '') === false) {
                        $errors[] = "Règle regex: pattern invalide";
                    }
                    break;
            }
        }

        return $errors;
    }

    private function validateConditionStructure(array $condition, int $ruleIndex): array
    {
        $errors = [];
        $requiredFields = ['field', 'operator', 'value'];

        foreach ($requiredFields as $field) {
            if (!isset($condition[$field])) {
                $errors[] = "Règle {$ruleIndex}: condition.{$field} manquant";
            }
        }

        if (isset($condition['operator'])) {
            $validOperators = ['=', '!=', '>', '<', '>=', '<=', 'contains', 'starts_with', 'ends_with', 'in', 'not_in'];
            if (!in_array($condition['operator'], $validOperators)) {
                $errors[] = "Règle {$ruleIndex}: opérateur '{$condition['operator']}' invalide";
            }
        }

        return $errors;
    }

    private function validateSingleParameterConfig(array $config, int $index): array
    {
        $errors = [];
        $required = ['parameter_name', 'order_position'];

        foreach ($required as $field) {
            if (!isset($config[$field])) {
                $errors[] = "Configuration {$index}: champ requis manquant - {$field}";
            }
        }

        if (isset($config['order_position'])) {
            if (!is_int($config['order_position']) || $config['order_position'] < 1) {
                $errors[] = "Configuration {$index}: order_position doit être un entier positif";
            }
        }

        if (isset($config['separator']) && strlen($config['separator']) > 10) {
            $errors[] = "Configuration {$index}: séparateur trop long (max 10 caractères)";
        }

        return $errors;
    }

    private function parseNomenclatureParts(string $nomenclature): array
    {
        // Support de différents séparateurs
        $separators = ['_', '-', '.'];
        $parts = [$nomenclature];

        foreach ($separators as $separator) {
            if (str_contains($nomenclature, $separator)) {
                $parts = explode($separator, $nomenclature);
                break;
            }
        }

        return array_filter($parts, fn($part) => !empty(trim($part)));
    }

    private function hasConsistentSeparators(string $nomenclature): bool
    {
        $separators = ['_', '-', '.'];
        $usedSeparators = [];

        foreach ($separators as $sep) {
            if (str_contains($nomenclature, $sep)) {
                $usedSeparators[] = $sep;
            }
        }

        // Retourne true si 0 ou 1 séparateur utilisé
        return count($usedSeparators) <= 1;
    }

    private function calculateNomenclatureScore(string $nomenclature, array $issues, array $suggestions): int
    {
        $score = 100;

        // Pénalités pour les problèmes
        $score -= count($issues) * 20;
        $score -= count($suggestions) * 5;

        // Bonus pour les bonnes pratiques
        if (strlen($nomenclature) <= 50) {
            $score += 5;
        }

        if (preg_match('/^\w+_\d{4}_/', $nomenclature)) {
            $score += 5; // Bonus pour pattern titre_année_
        }

        if ($this->hasConsistentSeparators($nomenclature)) {
            $score += 3;
        }

        return max(0, min(100, $score));
    }
}
