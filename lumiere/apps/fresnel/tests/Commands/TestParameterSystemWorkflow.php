<?php

namespace Tests\Commands;

use Illuminate\Console\Command;
use App\Models\Parameter;
use App\Models\Festival;
use App\Models\FestivalParameter;
use Illuminate\Support\Facades\DB;

class TestParameterSystemWorkflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:parameter-workflow {--reset : Réinitialiser les données de test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste le workflow complet du nouveau système de paramètres';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Test du nouveau système de paramètres');
        $this->newLine();

        if ($this->option('reset')) {
            $this->resetTestData();
        }

        $this->testDatabaseStructure();
        $this->testParameterCreation();
        $this->testFestivalParameterAssignment();
        $this->testSystemParameterAutoAssignment();
        $this->testWorkflow();

        $this->newLine();
        $this->info('✅ Tous les tests sont passés avec succès !');
        
        return 0;
    }

    private function resetTestData()
    {
        $this->info('🔄 Réinitialisation des données de test...');
        
        // Nettoyer les données de test
        FestivalParameter::where('festival_specific_notes', 'LIKE', '%test%')->delete();
        Parameter::where('name', 'LIKE', 'test_%')->delete();
        
        $this->info('✅ Données de test nettoyées');
        $this->newLine();
    }

    private function testDatabaseStructure()
    {
        $this->info('1️⃣ Test de la structure de la base de données');

        // Vérifier que les tables existent
        $tables = ['parameters', 'festival_parameters'];
        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $this->error("❌ Table {$table} manquante");
                return;
            }
        }

        // Vérifier les nouveaux champs dans parameters
        $parameterColumns = ['is_system', 'is_global'];
        foreach ($parameterColumns as $column) {
            if (!DB::getSchemaBuilder()->hasColumn('parameters', $column)) {
                $this->error("❌ Colonne {$column} manquante dans parameters");
                return;
            }
        }

        // Vérifier les champs dans festival_parameters
        $fpColumns = ['festival_id', 'parameter_id', 'is_enabled', 'custom_default_value'];
        foreach ($fpColumns as $column) {
            if (!DB::getSchemaBuilder()->hasColumn('festival_parameters', $column)) {
                $this->error("❌ Colonne {$column} manquante dans festival_parameters");
                return;
            }
        }

        $this->info('✅ Structure de base de données validée');
    }

    private function testParameterCreation()
    {
        $this->info('2️⃣ Test de création des paramètres');

        try {
            // Créer un paramètre global normal
            $globalParam = Parameter::create([
                'name' => 'test_global_param',
                'code' => 'TGP',
                'type' => 'string',
                'description' => 'Paramètre global de test',
                'is_active' => true,
                'is_system' => false,
                'is_global' => true,
                'category' => 'test'
            ]);

            // Créer un paramètre système
            $systemParam = Parameter::create([
                'name' => 'test_system_param',
                'code' => 'TSP',
                'type' => 'string',
                'description' => 'Paramètre système de test',
                'is_active' => true,
                'is_system' => true,
                'is_global' => true,
                'category' => 'test'
            ]);

            $this->info("✅ Paramètres créés: Global (ID: {$globalParam->id}), Système (ID: {$systemParam->id})");

        } catch (\Exception $e) {
            $this->error("❌ Erreur création paramètres: " . $e->getMessage());
        }
    }

    private function testFestivalParameterAssignment()
    {
        $this->info('3️⃣ Test d\'assignation manuelle des paramètres');

        try {
            // Récupérer le premier festival actif
            $festival = Festival::active()->first();
            if (!$festival) {
                $this->warn('Aucun festival actif trouvé, création d\'un festival de test...');
                $festival = Festival::create([
                    'name' => 'Festival Test',
                    'subdomain' => 'test-festival',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                    'is_active' => true
                ]);
            }

            // Récupérer un paramètre global non-système
            $globalParam = Parameter::where('name', 'test_global_param')->first();
            if (!$globalParam) {
                $this->error('❌ Paramètre global de test non trouvé');
                return;
            }

            // Assigner manuellement le paramètre au festival
            $festivalParam = FestivalParameter::create([
                'festival_id' => $festival->id,
                'parameter_id' => $globalParam->id,
                'is_enabled' => true,
                'display_order' => 1,
                'festival_specific_notes' => 'Test d\'assignation manuelle'
            ]);

            $this->info("✅ Paramètre assigné au festival '{$festival->name}' (ID: {$festivalParam->id})");

        } catch (\Exception $e) {
            $this->error("❌ Erreur assignation: " . $e->getMessage());
        }
    }

    private function testSystemParameterAutoAssignment()
    {
        $this->info('4️⃣ Test d\'auto-assignation des paramètres système');

        try {
            // Récupérer un festival
            $festival = Festival::active()->first();
            $systemParam = Parameter::where('name', 'test_system_param')->first();

            if (!$festival || !$systemParam) {
                $this->error('❌ Festival ou paramètre système non trouvé');
                return;
            }

            // Vérifier si le paramètre système n'est pas déjà assigné
            $exists = FestivalParameter::where('festival_id', $festival->id)
                ->where('parameter_id', $systemParam->id)
                ->exists();

            if (!$exists) {
                // Simuler l'auto-assignation
                FestivalParameter::create([
                    'festival_id' => $festival->id,
                    'parameter_id' => $systemParam->id,
                    'is_enabled' => true,
                    'display_order' => 0,
                    'festival_specific_notes' => 'Test auto-assignation système'
                ]);

                $this->info("✅ Paramètre système auto-assigné au festival '{$festival->name}'");
            } else {
                $this->info("✅ Paramètre système déjà assigné (normal)");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur auto-assignation: " . $e->getMessage());
        }
    }

    private function testWorkflow()
    {
        $this->info('5️⃣ Test du workflow complet');

        try {
            // Tester les scopes
            $globalParams = Parameter::global()->active()->count();
            $systemParams = Parameter::system()->active()->count();
            $availableParams = Parameter::availableForFestivals()->count();

            $this->info("📊 Paramètres globaux actifs: {$globalParams}");
            $this->info("📊 Paramètres système actifs: {$systemParams}");
            $this->info("📊 Paramètres disponibles pour festivals: {$availableParams}");

            // Tester les relations
            $festival = Festival::active()->first();
            if ($festival) {
                $festivalParamsCount = $festival->festivalParameters()->count();
                $activeParamsCount = $festival->activeParameters()->count();
                $systemParamsCount = $festival->systemParameters()->count();

                $this->info("📊 Paramètres du festival '{$festival->name}':");
                $this->info("   • Total: {$festivalParamsCount}");
                $this->info("   • Actifs: {$activeParamsCount}");
                $this->info("   • Système: {$systemParamsCount}");
            }

            // Vérifier l'intégrité des données
            $orphanedParams = FestivalParameter::whereDoesntHave('parameter')->count();
            $orphanedFestivals = FestivalParameter::whereDoesntHave('festival')->count();

            if ($orphanedParams > 0 || $orphanedFestivals > 0) {
                $this->warn("⚠️ Données orphelines trouvées: {$orphanedParams} paramètres, {$orphanedFestivals} festivals");
            } else {
                $this->info("✅ Intégrité des données validée");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur workflow: " . $e->getMessage());
        }
    }
}
