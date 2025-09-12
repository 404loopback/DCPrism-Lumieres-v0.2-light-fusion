<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Festival;
use App\Models\Parameter;
use App\Models\FestivalParameter;

class AssignSystemParametersToFestivals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'festivals:assign-system-parameters {--festival-id= : ID du festival spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assigne automatiquement les paramètres système à tous les festivals actifs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Assignation des paramètres système aux festivals...');

        // Récupérer les festivals à traiter
        $festivalsQuery = Festival::active();
        if ($festivalId = $this->option('festival-id')) {
            $festivalsQuery->where('id', $festivalId);
        }
        $festivals = $festivalsQuery->get();

        if ($festivals->isEmpty()) {
            $this->warn('Aucun festival actif trouvé.');
            return 1;
        }

        // Récupérer tous les paramètres système actifs
        $systemParameters = Parameter::system()->active()->get();

        if ($systemParameters->isEmpty()) {
            $this->warn('Aucun paramètre système trouvé.');
            return 1;
        }

        $this->info("Paramètres système à assigner: {$systemParameters->count()}");
        $this->info("Festivals à traiter: {$festivals->count()}");

        $totalAssigned = 0;
        $progressBar = $this->output->createProgressBar($festivals->count());

        foreach ($festivals as $festival) {
            $assignedForFestival = 0;

            foreach ($systemParameters as $parameter) {
                // Vérifier si le paramètre n'est pas déjà assigné
                $exists = FestivalParameter::where('festival_id', $festival->id)
                    ->where('parameter_id', $parameter->id)
                    ->exists();

                if (!$exists) {
                    FestivalParameter::create([
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                        'is_enabled' => true,
                        'display_order' => $parameter->sort_order ?? 0,
                        'festival_specific_notes' => 'Paramètre système assigné automatiquement via commande',
                    ]);

                    $assignedForFestival++;
                    $totalAssigned++;
                }
            }

            if ($assignedForFestival > 0) {
                $this->line("\n✅ Festival '{$festival->name}': {$assignedForFestival} paramètre(s) assigné(s)");
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);
        $this->info("🎉 Assignation terminée!");
        $this->info("Total des paramètres assignés: {$totalAssigned}");

        // Afficher un résumé des paramètres système
        $this->newLine();
        $this->info("📋 Paramètres système actifs:");
        foreach ($systemParameters as $parameter) {
            $this->line("  • {$parameter->name} ({$parameter->category})");
        }

        return 0;
    }
}
