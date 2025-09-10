<?php

namespace App\Console\Commands;

use App\Models\Festival;
use App\Models\Parameter;
use App\Models\FestivalParameter;
use Illuminate\Console\Command;

class EnsureRequiredParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'festival:ensure-required-parameters {--festival-id= : Specific festival ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all festivals have required and system parameters automatically added';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Adding required parameters to festivals...');
        
        // Récupérer tous les paramètres obligatoires (requis ou système)
        $requiredParameters = Parameter::where(function ($query) {
                $query->where('is_required', true)
                      ->orWhere('is_system', true);
            })
            ->where('is_active', true)
            ->get();
        
        if ($requiredParameters->isEmpty()) {
            $this->info('No required parameters found.');
            return;
        }
        
        $this->info("Found {$requiredParameters->count()} required parameters.");
        
        // Déterminer quels festivals traiter
        $festivalsQuery = Festival::query();
        
        if ($festivalId = $this->option('festival-id')) {
            $festivalsQuery->where('id', $festivalId);
        }
        
        $festivals = $festivalsQuery->get();
        
        if ($festivals->isEmpty()) {
            $this->error('No festivals found to process.');
            return;
        }
        
        $totalAdded = 0;
        
        // Pour chaque festival
        foreach ($festivals as $festival) {
            $this->line("Processing festival: {$festival->name} (ID: {$festival->id})");
            $addedForFestival = 0;
            
            foreach ($requiredParameters as $parameter) {
                // Vérifier si le paramètre existe déjà pour ce festival
                $exists = FestivalParameter::where('festival_id', $festival->id)
                    ->where('parameter_id', $parameter->id)
                    ->exists();
                
                if (!$exists) {
                    // Ajouter le paramètre obligatoire
                    FestivalParameter::create([
                        'festival_id' => $festival->id,
                        'parameter_id' => $parameter->id,
                        'is_enabled' => true,
                        'display_order' => $parameter->is_system ? -1 : 0,
                        'festival_specific_notes' => $parameter->is_system 
                            ? 'Paramètre système ajouté automatiquement' 
                            : 'Paramètre obligatoire ajouté automatiquement',
                    ]);
                    
                    $addedForFestival++;
                    $totalAdded++;
                    
                    $this->line("  ✓ Added: {$parameter->name} (" . 
                        ($parameter->is_system ? 'System' : 'Required') . ")");
                }
            }
            
            if ($addedForFestival === 0) {
                $this->line("  ✓ No new parameters needed for this festival.");
            }
        }
        
        $this->newLine();
        $this->info("Process completed!");
        $this->info("Total parameters added: {$totalAdded}");
        $this->info("Festivals processed: {$festivals->count()}");
        
        return 0;
    }
}
