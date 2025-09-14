<?php

namespace Database\Seeders;

use Modules\Fresnel\app\Models\Parameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Langue Audio',
                'code' => 'AUDIO_LANG',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Code de la langue audio principale (FR, EN, etc.)',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Type Version',
                'code' => 'VERSION_TYPE',
                'type' => 'string',
                'category' => 'content',
                'description' => 'Type de version (VO, VOST, VF, DUB)',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Année',
                'code' => 'YEAR',
                'type' => 'int',
                'category' => 'content',
                'description' => 'Année de production du film',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Format',
                'code' => 'FORMAT',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Format technique du DCP (2K, 4K, etc.)',
                'is_required' => false,
                'is_active' => true,
            ],
            // Paramètres additionnels pour nomenclature avancée
            [
                'name' => 'Durée',
                'code' => 'DURATION',
                'type' => 'int',
                'category' => 'technical',
                'description' => 'Durée du film en minutes',
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ratio',
                'code' => 'ASPECT_RATIO',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Ratio d\'affichage (1.85, 2.39, etc.)',
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Frame Rate',
                'code' => 'FRAME_RATE',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Fréquence d\'images (24fps, 25fps, etc.)',
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Sous-titres',
                'code' => 'SUBTITLES',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Code des langues de sous-titres',
                'is_required' => false,
                'is_active' => true,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($parameters as $paramData) {
            $parameter = Parameter::firstOrCreate(
                ['code' => $paramData['code']],
                $paramData
            );

            if ($parameter->wasRecentlyCreated) {
                $created++;
                $this->command->line("  ✅ Paramètre créé: {$paramData['name']} ({$paramData['code']})");
            } else {
                $updated++;
                $this->command->line("  ℹ️  Paramètre existant: {$paramData['name']} ({$paramData['code']})");
            }
        }

        $this->command->info("📊 Résultat: {$created} paramètres créés, {$updated} existants");
        $this->command->newLine();
    }
}
