<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;

class NomenclatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crée les nomenclatures par défaut pour les festivals existants.
     * Configuration: TITRE_ANNEE_VERSION_LANGUE
     */
    public function run(): void
    {
        $this->command->info('🌱 Configuration des nomenclatures par défaut...');

        // Vérifier que les paramètres existent
        $requiredParameters = ['TITLE', 'YEAR', 'VERSION_TYPE', 'AUDIO_LANG'];
        $parameters = [];

        foreach ($requiredParameters as $code) {
            $param = Parameter::where('code', $code)->first();
            if (! $param) {
                $this->command->error("❌ Paramètre manquant: {$code}");
                $this->command->info("🔄 Exécutez d'abord: php artisan db:seed --class=ParameterSeeder");

                return;
            }
            $parameters[$code] = $param;
        }

        // Configuration de nomenclature par défaut
        $defaultNomenclatureConfig = [
            [
                'parameter_code' => 'TITLE',
                'order_position' => 1,
                'separator' => '_',
                'prefix' => '',
                'suffix' => '',
            ],
            [
                'parameter_code' => 'YEAR',
                'order_position' => 2,
                'separator' => '_',
                'prefix' => '',
                'suffix' => '',
            ],
            [
                'parameter_code' => 'VERSION_TYPE',
                'order_position' => 3,
                'separator' => '_',
                'prefix' => '',
                'suffix' => '',
            ],
            [
                'parameter_code' => 'AUDIO_LANG',
                'order_position' => 4,
                'separator' => '',
                'prefix' => '',
                'suffix' => '',
            ],
        ];

        $festivals = Festival::all();

        if ($festivals->isEmpty()) {
            $this->command->warn("⚠️  Aucun festival trouvé. Création d'un festival de test...");

            $festival = Festival::create([
                'name' => 'Festival de Test',
                'subdomain' => 'test',
                'description' => 'Festival pour tester la nomenclature',
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(2)->addDays(10),
                'is_active' => true,
            ]);

            $festivals = collect([$festival]);
            $this->command->info("✅ Festival de test créé: {$festival->name}");
        }

        $totalCreated = 0;
        $totalExisting = 0;

        foreach ($festivals as $festival) {
            $this->command->line("🎭 Configuration pour: {$festival->name}");

            $festivalCreated = 0;
            $festivalExisting = 0;

            foreach ($defaultNomenclatureConfig as $config) {
                $parameter = $parameters[$config['parameter_code']];

                $nomenclature = Nomenclature::firstOrCreate(
                    [
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                    ],
                    [
                        'order_position' => $config['order_position'],
                        'separator' => $config['separator'],
                        'prefix' => $config['prefix'],
                        'suffix' => $config['suffix'],
                        'is_active' => true,
                    ]
                );

                if ($nomenclature->wasRecentlyCreated) {
                    $festivalCreated++;
                    $this->command->line("  ✅ {$parameter->name} (position {$config['order_position']})");
                } else {
                    $festivalExisting++;
                    $this->command->line("  ℹ️  {$parameter->name} (existant)");
                }
            }

            $totalCreated += $festivalCreated;
            $totalExisting += $festivalExisting;

            $this->command->line("  📊 {$festivalCreated} éléments créés, {$festivalExisting} existants");
        }

        $this->command->info('✨ Configuration terminée!');
        $this->command->info("📊 Total: {$totalCreated} nomenclatures créées sur ".$festivals->count().' festival(s)');

        // Afficher un exemple de nomenclature générée
        $this->command->newLine();
        $this->command->info('📋 Exemple de nomenclature générée: TITRE_2025_VO_FR');
    }
}
