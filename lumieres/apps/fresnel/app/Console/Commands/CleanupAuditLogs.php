<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuditService;

class CleanupAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:cleanup {--days=730 : Nombre de jours de rétention des logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoie les logs d\'audit anciens selon les règles de rétention GDPR';

    /**
     * Execute the console command.
     */
    public function handle(AuditService $auditService)
    {
        $retentionDays = (int) $this->option('days');
        
        $this->info("Nettoyage des logs d'audit plus anciens que {$retentionDays} jours...");
        
        $deletedCount = $auditService->cleanupOldLogs($retentionDays);
        
        if ($deletedCount > 0) {
            $this->info("✅ {$deletedCount} logs d'audit ont été supprimés.");
        } else {
            $this->info('ℹ️ Aucun log d\'audit à supprimer.');
        }
        
        return Command::SUCCESS;
    }
}
