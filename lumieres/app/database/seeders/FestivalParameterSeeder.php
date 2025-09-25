<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Models\FestivalParameter;

class FestivalParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crée les associations festival-paramètres avec la logique système.
     * Les paramètres système sont automatiquement ajoutés à tous les festivals.
     */
    public function run(): void
    {
        $this->command->info('🔗 Création des associations festival-paramètres...');

        $festivals = Festival::all();
        $systemParameters = Parameter::where('is_system', true)->get();
        $customParameters = Parameter::where('is_system', false)->get();

        $created = 0;
        $skipped = 0;

        foreach ($festivals as $festival) {
            $this->command->line("  📋 Festival: {$festival->name}");

            // 1. Ajouter automatiquement tous les paramètres système
            foreach ($systemParameters as $parameter) {
                $festivalParameter = FestivalParameter::firstOrCreate(
                    [
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                    ],
                    [
                        'is_enabled' => true,
                        'is_required' => true, // Les paramètres système sont toujours requis
                        'is_visible_in_nomenclature' => true, // Visible par défaut
                        'is_system' => true, // Marqué comme système
                        'custom_default_value' => null,
                        'custom_formatting_rules' => null,
                        'display_order' => $this->getSystemParameterOrder($parameter->code),
                        'festival_specific_notes' => "Paramètre système requis pour {$festival->name}",
                    ]
                );

                if ($festivalParameter->wasRecentlyCreated) {
                    $created++;
                    $this->command->line("    ✅ Paramètre système ajouté: {$parameter->name} ({$parameter->code})");
                } else {
                    $skipped++;
                    $this->command->line("    ℹ️  Paramètre système existant: {$parameter->name} ({$parameter->code})");
                }
            }

            // 2. Ajouter quelques paramètres personnalisés selon le type de festival
            $this->addCustomParametersForFestival($festival, $customParameters);
        }

        $this->command->info("📊 Résultat: {$created} associations créées, {$skipped} existantes");
        $this->command->newLine();
    }

    /**
     * Définit l'ordre d'affichage des paramètres système
     */
    private function getSystemParameterOrder(string $code): int
    {
        $order = [
            'TITLE' => 1,
            'YEAR' => 2,
            'AUDIO_LANG' => 3,
            'VERSION_TYPE' => 4,
        ];

        return $order[$code] ?? 999;
    }

    /**
     * Ajoute des paramètres personnalisés selon le type de festival
     */
    private function addCustomParametersForFestival(Festival $festival, $customParameters)
    {
        // Logique spécifique selon le festival
        $festivalCustomParams = [];

        switch (strtolower($festival->subdomain)) {
            case 'cannes':
                // Cannes est très technique, ajoute tous les paramètres techniques
                $festivalCustomParams = ['FORMAT', 'ASPECT_RATIO', 'FRAME_RATE', 'SUBTITLES', 'DURATION'];
                break;

            case 'berlinale':
                // Berlinale focus sur format et subtitles
                $festivalCustomParams = ['FORMAT', 'SUBTITLES', 'DURATION'];
                break;

            case 'clermont-ferrand':
                // Courts métrages - durée obligatoire, format optionnel
                $festivalCustomParams = ['DURATION', 'FORMAT'];
                break;

            default:
                // Par défaut, ajouter les paramètres les plus courants
                $festivalCustomParams = ['FORMAT', 'DURATION'];
                break;
        }

        foreach ($customParameters as $parameter) {
            if (in_array($parameter->code, $festivalCustomParams)) {
                $festivalParameter = FestivalParameter::firstOrCreate(
                    [
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                    ],
                    [
                        'is_enabled' => true,
                        'is_required' => $this->isCustomParameterRequired($parameter->code, $festival),
                        'is_visible_in_nomenclature' => true,
                        'is_system' => false, // Paramètre personnalisé
                        'custom_default_value' => $this->getCustomDefaultValue($parameter->code, $festival),
                        'custom_formatting_rules' => null,
                        'display_order' => $this->getCustomParameterOrder($parameter->code),
                        'festival_specific_notes' => $this->getCustomParameterNotes($parameter->code, $festival),
                    ]
                );

                if ($festivalParameter->wasRecentlyCreated) {
                    $this->command->line("    ✅ Paramètre personnalisé ajouté: {$parameter->name} ({$parameter->code})");
                }
            }
        }
    }

    /**
     * Détermine si un paramètre personnalisé est requis pour un festival
     */
    private function isCustomParameterRequired(string $code, Festival $festival): bool
    {
        $requiredByFestival = [
            'cannes' => ['FORMAT', 'SUBTITLES'],
            'clermont-ferrand' => ['DURATION'], // Obligatoire pour les courts métrages
        ];

        $festivalKey = strtolower($festival->subdomain);
        return in_array($code, $requiredByFestival[$festivalKey] ?? []);
    }

    /**
     * Ordre d'affichage des paramètres personnalisés
     */
    private function getCustomParameterOrder(string $code): int
    {
        $order = [
            'FORMAT' => 10,
            'DURATION' => 11,
            'ASPECT_RATIO' => 12,
            'FRAME_RATE' => 13,
            'SUBTITLES' => 14,
        ];

        return $order[$code] ?? 999;
    }

    /**
     * Valeur par défaut personnalisée selon le festival
     */
    private function getCustomDefaultValue(string $code, Festival $festival): ?string
    {
        $defaults = [
            'cannes' => [
                'FORMAT' => '4K',
                'FRAME_RATE' => '24',
            ],
            'clermont-ferrand' => [
                'FORMAT' => '2K',
                'DURATION' => '15',
            ],
        ];

        $festivalKey = strtolower($festival->subdomain);
        return $defaults[$festivalKey][$code] ?? null;
    }

    /**
     * Notes spécifiques au festival pour un paramètre
     */
    private function getCustomParameterNotes(string $code, Festival $festival): ?string
    {
        $notes = [
            'cannes' => [
                'FORMAT' => 'Format 4K privilégié pour la sélection officielle',
                'SUBTITLES' => 'Sous-titres français obligatoires pour tous les films',
                'ASPECT_RATIO' => 'Respecter le format original de l\'œuvre',
            ],
            'clermont-ferrand' => [
                'DURATION' => 'Durée maximale de 30 minutes pour les courts métrages',
                'FORMAT' => 'Format 2K acceptable pour les productions indépendantes',
            ],
        ];

        $festivalKey = strtolower($festival->subdomain);
        return $notes[$festivalKey][$code] ?? null;
    }
}
