<?php

namespace App\Console\Commands;

use App\Services\MonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CollectMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect
                          {--store : Store metrics in database}
                          {--alerts : Generate and send alerts}
                          {--details : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect system and application metrics for monitoring';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringService $monitoring)
    {
        $this->info('🎬 DCPrism Metrics Collection');
        $this->info('============================');
        
        try {
            // Collecte des métriques
            $this->info('Collecting metrics...');
            $metrics = $monitoring->collectMetrics();
            
            if ($this->option('details')) {
                $this->displayMetrics($metrics);
            }
            
            // Stockage des métriques si demandé
            if ($this->option('store')) {
                $this->info('Storing metrics...');
                $monitoring->storeMetrics();
                $this->info('✅ Metrics stored successfully');
            }
            
            // Génération et envoi des alertes si demandé
            if ($this->option('alerts')) {
                $this->info('Generating alerts...');
                $alerts = $monitoring->generateAlerts();
                
                if (!empty($alerts)) {
                    $this->displayAlerts($alerts);
                    
                    // Envoie les alertes critiques
                    $monitoring->sendCriticalAlerts();
                    $this->info('✅ Critical alerts sent');
                } else {
                    $this->info('✅ No alerts generated');
                }
            }
            
            $this->newLine();
            $this->info('✅ Metrics collection completed successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Error during metrics collection: ' . $e->getMessage());
            Log::error('Metrics collection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Affiche les métriques collectées
     */
    private function displayMetrics(array $metrics): void
    {
        $this->newLine();
        $this->info('📊 Collected Metrics:');
        $this->info('-------------------');
        
        // Métriques système
        if (isset($metrics['system'])) {
            $this->line('<fg=cyan>System Metrics:</fg=cyan>');
            $system = $metrics['system'];
            
            $this->line("  • Memory Usage: {$system['memory_usage']}%");
            $this->line("  • CPU Usage: {$system['cpu_usage']}%");
            $this->line("  • Disk Usage: {$system['disk_usage']}%");
            $this->line("  • Queue Size: {$system['queue_size']} jobs");
            $this->line("  • Cache Hit Ratio: {$system['cache_hit_ratio']}%");
            $this->newLine();
        }
        
        // Métriques applicatives
        if (isset($metrics['application'])) {
            $this->line('<fg=green>Application Metrics:</fg=green>');
            $app = $metrics['application'];
            
            $this->line("  • Total Movies: {$app['total_movies']}");
            $this->line("  • Movies with DCP: {$app['movies_with_dcp']}");
            $this->line("  • DCP Processing Rate: {$app['dcp_processing_rate']}%");
            $this->line("  • Total Festivals: {$app['total_festivals']}");
            $this->line("  • Active Festivals: {$app['active_festivals']}");
            $this->line("  • Total Users: {$app['total_users']}");
            $this->line("  • Active Users (24h): {$app['active_users_24h']}");
            $this->line("  • Running Jobs: {$app['running_jobs']}");
            $this->line("  • Failed Jobs (24h): {$app['failed_jobs_24h']}");
            $this->newLine();
        }
        
        // Métriques de performance
        if (isset($metrics['performance'])) {
            $this->line('<fg=yellow>Performance Metrics:</fg=yellow>');
            $perf = $metrics['performance'];
            
            $this->line("  • Average Response Time: {$perf['response_time_avg']}ms");
            $this->line("  • Throughput: {$perf['throughput']} req/s");
            $this->line("  • Error Rate: {$perf['error_rate']}%");
            $this->line("  • Job Success Rate: {$perf['job_success_rate']}%");
            $this->line("  • API Requests (24h): {$perf['api_requests_24h']}");
            $this->newLine();
        }
        
        // Métriques business
        if (isset($metrics['business'])) {
            $this->line('<fg=magenta>Business Metrics:</fg=magenta>');
            $business = $metrics['business'];
            
            $this->line("  • DCP Volume Processed: {$business['dcp_volume_processed']} GB");
            
            if (isset($business['festivals_submissions'])) {
                $submissions = $business['festivals_submissions'];
                $this->line("  • New Submissions: {$submissions['new_submissions']}");
                $this->line("  • Pending Review: {$submissions['pending_review']}");
                $this->line("  • Approved: {$submissions['approved']}");
            }
            
            $this->newLine();
        }
    }
    
    /**
     * Affiche les alertes générées
     */
    private function displayAlerts(array $alerts): void
    {
        $this->newLine();
        $this->info('🚨 Generated Alerts:');
        $this->info('------------------');
        
        foreach ($alerts as $alert) {
            $color = match($alert['level']) {
                'critical' => 'red',
                'warning' => 'yellow',
                'info' => 'blue',
                default => 'white',
            };
            
            $icon = match($alert['level']) {
                'critical' => '🔴',
                'warning' => '🟡',
                'info' => '🔵',
                default => '⚪',
            };
            
            $this->line("<fg={$color}>{$icon} [{$alert['level']}] {$alert['title']}</fg={$color}>");
            $this->line("    {$alert['message']}");
            $this->line("    Time: {$alert['timestamp']->format('Y-m-d H:i:s')}");
            $this->newLine();
        }
    }
}
