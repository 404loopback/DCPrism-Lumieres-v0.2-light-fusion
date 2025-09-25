<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Parameter;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crée les paramètres de base pour le système de nomenclature DCP.
     */
    public function run(): void
    {
        $this->command->info('🌱 Création des paramètres de nomenclature...');

        $parameters = [
            [
                'name' => 'Titre',
                'code' => 'TITLE',
                'type' => 'string',
                'category' => 'content',
                'description' => 'Titre du film pour la nomenclature DCP',
                'short_description' => 'Titre principal du film',
                'detailed_description' => 'Le titre officiel du film tel qu\'il apparaîtra dans la nomenclature DCP. Doit être exact et sans caractères spéciaux.',
                'example_value' => 'OPPENHEIMER',
                'use_cases' => ["Titre français", "Titre original", "Titre international"],
                'icon' => 'film',
                'color' => 'blue',
                'is_required' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Langue Audio',
                'code' => 'AUDIO_LANG',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Code de la langue audio principale (FR, EN, etc.)',
                'short_description' => 'Langue audio principale',
                'detailed_description' => 'Code ISO 639-1 de la langue audio principale du film. Utilisé pour identifier la piste audio dominante dans le DCP.',
                'example_value' => 'FR',
                'use_cases' => ["Langue originale", "Langue de doublage", "Version multilingue"],
                'icon' => 'speaker-wave',
                'color' => 'green',
                'is_required' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Type Version',
                'code' => 'VERSION_TYPE',
                'type' => 'string',
                'category' => 'content',
                'description' => 'Type de version (VO, VOST, DUB, DUBST, MUTE)',
                'short_description' => 'Type de version linguistique',
                'detailed_description' => 'Spécifie si le film est en version originale, sous-titrée, doublée ou muette. Essentiel pour la classification des contenus.',
                'example_value' => 'VOST',
                'possible_values' => ["VO", "VOST", "DUB", "DUBST", "MUTE"],
                'use_cases' => ["VO (Version Originale)", "VOST (Version Originale Sous-Titrée)", "DUB (Doublée)", "DUBST (Doublée Sous-Titrée)", "MUTE (Muette)"],
                'icon' => 'language',
                'color' => 'purple',
                'is_required' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Année',
                'code' => 'YEAR',
                'type' => 'int',
                'category' => 'content',
                'description' => 'Année de production du film',
                'short_description' => 'Année de production',
                'detailed_description' => 'Année de production officielle du film. Utilisée pour la catégorisation temporelle et les règles de diffusion.',
                'example_value' => '2023',
                'use_cases' => ["Année de sortie", "Année de production", "Classification par décennie"],
                'icon' => 'calendar-days',
                'color' => 'orange',
                'is_required' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Format',
                'code' => 'FORMAT',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Format technique du DCP (2K, 4K, etc.)',
                'short_description' => 'Résolution technique',
                'detailed_description' => 'Résolution de l’image du DCP. Détermine la qualité et la compatibilité avec les systèmes de projection.',
                'example_value' => '4K',
                'use_cases' => ["2K (Standard)", "4K (Haute définition)", "8K (Ultra HD)"],
                'icon' => 'tv',
                'color' => 'indigo',
                'is_required' => false,
                'is_active' => true,
                'is_system' => false,
            ],
            // Paramètres additionnels pour nomenclature avancée
            [
                'name' => 'Durée',
                'code' => 'DURATION',
                'type' => 'int',
                'category' => 'technical',
                'description' => 'Durée du film en minutes',
                'short_description' => 'Durée totale',
                'detailed_description' => 'Durée totale du film en minutes, incluant les génériques. Importante pour la programmation et les contraintes techniques.',
                'example_value' => '148',
                'use_cases' => ["Court métrage (<30min)", "Long métrage (>90min)", "Format TV (52min)"],
                'icon' => 'clock',
                'color' => 'yellow',
                'is_required' => false,
                'is_active' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Ratio',
                'code' => 'ASPECT_RATIO',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Ratio d\'affichage (1.85, 2.39, etc.)',
                'short_description' => 'Format d\'image',
                'detailed_description' => 'Rapport largeur/hauteur de l\'image. Détermine le format de projection et l\'adaptation aux écrans.',
                'example_value' => '2.39',
                'use_cases' => ["1.85 (Standard)", "2.39 (Cinémascope)", "1.33 (Académie)", "16:9 (HD)"],
                'icon' => 'rectangle-group',
                'color' => 'pink',
                'is_required' => false,
                'is_active' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Frame Rate',
                'code' => 'FRAME_RATE',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Fréquence d\'images (24fps, 25fps, etc.)',
                'short_description' => 'Images par seconde',
                'detailed_description' => 'Nombre d\'images affichées par seconde. Impact sur la fluidité et la compatibilité régionale des projections.',
                'example_value' => '24',
                'use_cases' => ["24fps (Cinéma)", "25fps (PAL/Europe)", "30fps (NTSC/Amérique)", "48fps (HFR)"],
                'icon' => 'forward',
                'color' => 'teal',
                'is_required' => false,
                'is_active' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Sous-titres',
                'code' => 'SUBTITLES',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Code des langues de sous-titres',
                'short_description' => 'Langues de sous-titrage',
                'detailed_description' => 'Codes des langues disponibles pour le sous-titrage. Essentiels pour l\'accessibilité et la diffusion internationale.',
                'example_value' => 'FR-EN',
                'use_cases' => ["Sous-titres français", "Sous-titres multilingues", "Sous-titres malentendants"],
                'icon' => 'chat-bubble-bottom-center-text',
                'color' => 'cyan',
                'is_required' => false,
                'is_active' => true,
                'is_system' => false,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($parameters as $paramData) {
            $parameter = Parameter::updateOrCreate(
                ['code' => $paramData['code']],
                $paramData
            );

            if ($parameter->wasRecentlyCreated) {
                $created++;
                $this->command->line("  ✅ Paramètre créé: {$paramData['name']} ({$paramData['code']})");
            } else {
                $updated++;
                $this->command->line("  🔄 Paramètre mis à jour: {$paramData['name']} ({$paramData['code']})");
            }
        }

        $this->command->info("📊 Résultat: {$created} paramètres créés, {$updated} existants");
        $this->command->newLine();
    }
}
