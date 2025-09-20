<?php

namespace Modules\Fresnel\app\Services;

use Illuminate\Support\Facades\Log;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Parameter;

/**
 * Service pour implémenter les logiques spéciales des paramètres personnalisés
 * - Incrémentation automatique
 * - Initiales de festival/cinéma
 * - Formats de date
 * - Logiques personnalisées
 */
class ParameterLogicService
{
    /**
     * Générer une valeur selon la logique du paramètre
     */
    public function generateValue(Parameter $parameter, Festival $festival, Movie $movie = null, array $context = []): string
    {
        if (!$parameter->extraction_pattern) {
            return $parameter->default_value ?? '';
        }

        $config = json_decode($parameter->extraction_pattern, true);
        if (!$config || !isset($config['type'])) {
            return $parameter->default_value ?? '';
        }

        try {
            return match ($config['type']) {
                'increment' => $this->generateIncrement($festival, $parameter, $config),
                'festival_initials' => $this->generateFestivalInitials($festival, $config),
                'cinema_initials' => $this->generateCinemaInitials($movie, $config),
                'date_format' => $this->generateDateFormat($config, $context),
                'custom' => $this->executeCustomLogic($config, $festival, $movie, $context),
                default => $parameter->default_value ?? ''
            };
        } catch (\Exception $e) {
            Log::error('[ParameterLogicService] Failed to generate value', [
                'parameter_id' => $parameter->id,
                'parameter_name' => $parameter->name,
                'logic_type' => $config['type'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return $parameter->default_value ?? '';
        }
    }

    /**
     * Générer un numéro d'incrémentation
     */
    private function generateIncrement(Festival $festival, Parameter $parameter, array $config): string
    {
        $start = $config['start'] ?? 1;
        $padding = $config['padding'] ?? 3;
        
        // Compter combien de films utilisent déjà ce paramètre pour ce festival
        $count = $festival->movies()
            ->whereHas('movieParameters', function ($query) use ($parameter) {
                $query->where('parameter_id', $parameter->id)
                    ->whereNotNull('value')
                    ->where('value', '!=', '');
            })
            ->count();

        $nextNumber = $start + $count;
        
        return str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * Générer les initiales du festival
     */
    private function generateFestivalInitials(Festival $festival, array $config): string
    {
        $length = $config['length'] ?? 3;
        $name = $festival->name;
        
        // Extraire les premières lettres des mots significatifs
        $words = preg_split('/[\s\-_]+/', $name);
        $initials = '';
        
        foreach ($words as $word) {
            if (strlen($word) > 0 && !in_array(strtolower($word), ['de', 'du', 'des', 'le', 'la', 'les', 'et', 'of', 'the', 'and'])) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= $length) {
                    break;
                }
            }
        }
        
        // Si pas assez d'initiales, prendre les premiers caractères
        if (strlen($initials) < $length) {
            $cleanName = preg_replace('/[^a-zA-Z]/', '', $name);
            $initials = strtoupper(substr($cleanName, 0, $length));
        }
        
        return substr($initials, 0, $length);
    }

    /**
     * Générer les initiales du cinéma
     */
    private function generateCinemaInitials(Movie $movie = null, array $config = []): string
    {
        $length = $config['length'] ?? 3;
        
        // Pour l'instant, retourner une valeur par défaut
        // À implémenter quand la relation movie-cinema sera disponible
        if (!$movie) {
            return str_repeat('C', $length);
        }
        
        // TODO: Implémenter quand cinema sera disponible dans le modèle Movie
        // $cinema = $movie->cinema;
        // return $this->extractInitials($cinema->name, $length);
        
        return strtoupper(substr('CINEMA', 0, $length));
    }

    /**
     * Générer un format de date
     */
    private function generateDateFormat(array $config, array $context = []): string
    {
        $format = $config['format'] ?? 'Y';
        $date = $context['date'] ?? now();
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }

    /**
     * Exécuter une logique personnalisée
     */
    private function executeCustomLogic(array $config, Festival $festival, Movie $movie = null, array $context = []): string
    {
        $logic = $config['logic'] ?? '';
        
        // Logiques personnalisées prédéfinies
        return match ($logic) {
            'festival_year' => $festival->start_date ? $festival->start_date->format('Y') : now()->format('Y'),
            'festival_month' => $festival->start_date ? $festival->start_date->format('m') : now()->format('m'),
            'festival_day' => $festival->start_date ? $festival->start_date->format('d') : now()->format('d'),
            'movie_id_padded' => $movie ? str_pad($movie->id, 4, '0', STR_PAD_LEFT) : '0000',
            'random_string' => strtoupper(\Str::random(3)),
            'timestamp' => now()->format('YmdHis'),
            default => $config['default'] ?? ''
        };
    }

    /**
     * Valider une configuration de logique
     */
    public function validateLogicConfig(string $logicType, array $config): array
    {
        $errors = [];

        switch ($logicType) {
            case 'increment':
                if (isset($config['start']) && (!is_int($config['start']) || $config['start'] < 1)) {
                    $errors[] = 'La valeur de départ doit être un entier positif';
                }
                if (isset($config['padding']) && (!is_int($config['padding']) || $config['padding'] < 1 || $config['padding'] > 10)) {
                    $errors[] = 'Le padding doit être entre 1 et 10';
                }
                break;

            case 'festival_initials':
            case 'cinema_initials':
                if (isset($config['length']) && (!is_int($config['length']) || $config['length'] < 1 || $config['length'] > 10)) {
                    $errors[] = 'La longueur doit être entre 1 et 10 caractères';
                }
                break;

            case 'date_format':
                if (empty($config['format'])) {
                    $errors[] = 'Le format de date est requis';
                } else {
                    // Tester le format
                    try {
                        now()->format($config['format']);
                    } catch (\Exception $e) {
                        $errors[] = 'Format de date invalide';
                    }
                }
                break;

            case 'custom_logic':
                if (empty($config['logic'])) {
                    $errors[] = 'La logique personnalisée est requise';
                }
                break;
        }

        return $errors;
    }

    /**
     * Obtenir un exemple de valeur générée
     */
    public function getExampleValue(string $logicType, array $config, Festival $festival = null): string
    {
        $festival = $festival ?? new Festival(['name' => 'Festival Example', 'start_date' => now()]);
        
        return match ($logicType) {
            'increment' => $this->generateIncrement($festival, new Parameter(), $config),
            'festival_initials' => $this->generateFestivalInitials($festival, $config),
            'cinema_initials' => $this->generateCinemaInitials(null, $config),
            'date_format' => $this->generateDateFormat($config),
            'custom_logic' => $this->executeCustomLogic($config, $festival),
            default => 'EXEMPLE'
        };
    }

    /**
     * Obtenir les types de logique disponibles avec exemples
     */
    public static function getAvailableLogicTypes(): array
    {
        return [
            'increment' => [
                'name' => 'Incrémentation',
                'description' => 'Génère des numéros séquentiels',
                'example' => '001, 002, 003...',
                'config_fields' => [
                    'start' => ['type' => 'number', 'label' => 'Début', 'default' => 1],
                    'padding' => ['type' => 'number', 'label' => 'Zéros', 'default' => 3]
                ]
            ],
            'festival_initials' => [
                'name' => 'Initiales festival',
                'description' => 'Premières lettres du nom du festival',
                'example' => 'CAN, BER, CLF...',
                'config_fields' => [
                    'length' => ['type' => 'number', 'label' => 'Longueur', 'default' => 3]
                ]
            ],
            'cinema_initials' => [
                'name' => 'Initiales cinéma',
                'description' => 'Premières lettres du cinéma',
                'example' => 'REX, GAU, MUL...',
                'config_fields' => [
                    'length' => ['type' => 'number', 'label' => 'Longueur', 'default' => 3]
                ]
            ],
            'date_format' => [
                'name' => 'Format date',
                'description' => 'Date selon un format PHP',
                'example' => '2025, 25, 0917...',
                'config_fields' => [
                    'format' => ['type' => 'text', 'label' => 'Format PHP', 'default' => 'Y', 'help' => 'Y=année, m=mois, d=jour']
                ]
            ],
            'custom_logic' => [
                'name' => 'Logique personnalisée',
                'description' => 'Règles prédéfinies spéciales',
                'example' => 'festival_year, movie_id_padded...',
                'config_fields' => [
                    'logic' => [
                        'type' => 'select', 
                        'label' => 'Logique',
                        'options' => [
                            'festival_year' => 'Année du festival',
                            'festival_month' => 'Mois du festival',
                            'festival_day' => 'Jour du festival',
                            'movie_id_padded' => 'ID film avec zéros',
                            'random_string' => 'Chaîne aléatoire',
                            'timestamp' => 'Timestamp complet'
                        ]
                    ],
                    'default' => ['type' => 'text', 'label' => 'Valeur par défaut']
                ]
            ]
        ];
    }
}
