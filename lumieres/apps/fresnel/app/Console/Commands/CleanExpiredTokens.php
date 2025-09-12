<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanExpiredTokens extends Command
{
    protected $signature = 'auth:clean-tokens {--days=90 : Delete tokens not used for X days}';
    protected $description = 'Clean expired and unused authentication tokens';

    public function handle()
    {
        $days = $this->option('days');
        
        // Supprimer les tokens expirés
        $expiredCount = PersonalAccessToken::where('expires_at', '<', now())->count();
        PersonalAccessToken::where('expires_at', '<', now())->delete();
        
        // Supprimer les tokens non utilisés depuis X jours
        $inactiveCount = PersonalAccessToken::where('last_used_at', '<', now()->subDays($days))
            ->orWhere(function($query) use ($days) {
                $query->whereNull('last_used_at')
                      ->where('created_at', '<', now()->subDays($days));
            })
            ->count();
            
        PersonalAccessToken::where('last_used_at', '<', now()->subDays($days))
            ->orWhere(function($query) use ($days) {
                $query->whereNull('last_used_at')
                      ->where('created_at', '<', now()->subDays($days));
            })
            ->delete();
        
        $this->info("Tokens nettoyés :");
        $this->info("- Expirés : {$expiredCount}");
        $this->info("- Inactifs (+{$days} jours) : {$inactiveCount}");
        
        return 0;
    }
}
