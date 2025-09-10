<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class MonitoringService
{
    private array $metrics = [];
    private array $alerts = [];
    
    /**
     * Collecte toutes les métriques système
     */
    public function collectMetrics(): array
    {
        $this->collectSystemMetrics();
        $this->collectApplicationMetrics();
        $this->collectPerformanceMetrics();
        $this->collectBusinessMetrics();
        
        return $this->metrics;
    }
    
    /**
     * Métriques système (infrastructure)
     */
    private function collectSystemMetrics(): void
    {
        $this->metrics['system'] = [
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'database_connections' => $this->getDatabaseConnections(),
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'queue_size' => $this->getQueueSize(),
            'uptime' => $this->getUptime(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }
    
    /**
     * Métriques applicatives (DCPrism spécifique)
     */
    private function collectApplicationMetrics(): void
    {
        $this->metrics['application'] = [
            'total_movies' => Movie::count(),
            'movies_with_dcp' => Movie::whereNotNull('dcp_path')->count(),
            'total_festivals' => Festival::count(),
            'active_festivals' => Festival::where('status', 'ongoing')
                                          ->orWhere(function($q) {
                                              $q->where('start_date', '<=', Carbon::now())
                                                ->where('end_date', '>=', Carbon::now());
                                          })->count(),
            'total_users' => User::count(),
            'active_users_24h' => $this->getActiveUsers24h(),
            'total_jobs' => Job::count(),
            'pending_jobs' => Job::where('status', 'pending')->count(),
            'running_jobs' => Job::where('status', 'running')->count(),
            'failed_jobs_24h' => Job::where('status', 'failed')
                                   ->where('created_at', '>=', Carbon::now()->subDay())
                                   ->count(),
            'dcp_processing_rate' => $this->getDcpProcessingRate(),
            'average_job_duration' => $this->getAverageJobDuration(),
        ];
    }
    
    /**
     * Métriques de performance
     */
    private function collectPerformanceMetrics(): void
    {
        $this->metrics['performance'] = [
            'response_time_avg' => $this->getAverageResponseTime(),
            'throughput' => $this->getThroughput(),
            'error_rate' => $this->getErrorRate(),
            'job_success_rate' => $this->getJobSuccessRate(),
            'api_requests_24h' => $this->getApiRequests24h(),
            'slow_queries_count' => $this->getSlowQueriesCount(),
            'cache_performance' => $this->getCachePerformance(),
        ];
    }
    
    /**
     * Métriques business
     */
    private function collectBusinessMetrics(): void
    {
        $this->metrics['business'] = [
            'dcp_volume_processed' => $this->getDcpVolumeProcessed(),
            'festivals_submissions' => $this->getFestivalSubmissions(),
            'user_engagement' => $this->getUserEngagement(),
            'storage_efficiency' => $this->getStorageEfficiency(),
            'processing_efficiency' => $this->getProcessingEfficiency(),
            'revenue_metrics' => $this->getRevenueMetrics(), // Si applicable
        ];
    }
    
    /**
     * Génère des alertes basées sur les métriques
     */
    public function generateAlerts(): array
    {
        $this->checkSystemAlerts();
        $this->checkApplicationAlerts();
        $this->checkPerformanceAlerts();
        
        return $this->alerts;
    }
    
    /**
     * Alertes système
     */
    private function checkSystemAlerts(): void
    {
        $metrics = $this->metrics['system'] ?? [];
        
        // Mémoire
        if (($metrics['memory_usage'] ?? 0) > 85) {
            $this->addAlert('critical', 'Mémoire système élevée', 
                "Usage mémoire: {$metrics['memory_usage']}%");
        }
        
        // CPU
        if (($metrics['cpu_usage'] ?? 0) > 80) {
            $this->addAlert('warning', 'CPU système élevé', 
                "Usage CPU: {$metrics['cpu_usage']}%");
        }
        
        // Disque
        if (($metrics['disk_usage'] ?? 0) > 90) {
            $this->addAlert('critical', 'Espace disque critique', 
                "Usage disque: {$metrics['disk_usage']}%");
        }
        
        // Queue
        if (($metrics['queue_size'] ?? 0) > 1000) {
            $this->addAlert('warning', 'Queue surchargée', 
                "Taille de la queue: {$metrics['queue_size']} jobs");
        }
    }
    
    /**
     * Alertes applicatives
     */
    private function checkApplicationAlerts(): void
    {
        $metrics = $this->metrics['application'] ?? [];
        
        // Jobs échoués
        if (($metrics['failed_jobs_24h'] ?? 0) > 10) {
            $this->addAlert('warning', 'Nombre élevé de jobs échoués', 
                "{$metrics['failed_jobs_24h']} jobs échoués dans les dernières 24h");
        }
        
        // Jobs en attente
        if (($metrics['pending_jobs'] ?? 0) > 100) {
            $this->addAlert('info', 'Backlog de jobs important', 
                "{$metrics['pending_jobs']} jobs en attente");
        }
        
        // Taux de traitement DCP
        if (($metrics['dcp_processing_rate'] ?? 0) < 50) {
            $this->addAlert('warning', 'Taux de traitement DCP bas', 
                "Seulement {$metrics['dcp_processing_rate']}% des films ont un DCP");
        }
    }
    
    /**
     * Alertes de performance
     */
    private function checkPerformanceAlerts(): void
    {
        $metrics = $this->metrics['performance'] ?? [];
        
        // Temps de réponse
        if (($metrics['response_time_avg'] ?? 0) > 2000) {
            $this->addAlert('warning', 'Temps de réponse élevé', 
                "Temps moyen: {$metrics['response_time_avg']}ms");
        }
        
        // Taux d'erreur
        if (($metrics['error_rate'] ?? 0) > 5) {
            $this->addAlert('critical', 'Taux d\'erreur élevé', 
                "Taux d'erreur: {$metrics['error_rate']}%");
        }
        
        // Taux de succès des jobs
        if (($metrics['job_success_rate'] ?? 0) < 90) {
            $this->addAlert('warning', 'Taux de succès des jobs bas', 
                "Taux de succès: {$metrics['job_success_rate']}%");
        }
    }
    
    /**
     * Ajoute une alerte
     */
    private function addAlert(string $level, string $title, string $message): void
    {
        $this->alerts[] = [
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'timestamp' => Carbon::now(),
            'acknowledged' => false,
        ];
        
        // Log l'alerte
        Log::channel('monitoring')->log($level, $title, [
            'message' => $message,
            'service' => 'monitoring',
        ]);
    }
    
    /**
     * Envoie les alertes critiques par email/Slack
     */
    public function sendCriticalAlerts(): void
    {
        $criticalAlerts = collect($this->alerts)
            ->where('level', 'critical')
            ->where('acknowledged', false);
        
        foreach ($criticalAlerts as $alert) {
            $this->sendAlert($alert);
        }
    }
    
    /**
     * Envoie une alerte
     */
    private function sendAlert(array $alert): void
    {
        // Implémentation email/Slack
        Log::emergency('ALERTE CRITIQUE: ' . $alert['title'], [
            'message' => $alert['message'],
            'timestamp' => $alert['timestamp'],
        ]);
        
        // Implémentation envoi email via MailingService
        $mailingService = app(\App\Services\MailingService::class);
        $emailSent = $mailingService->sendMonitoringAlert($alert);
    }
    
    /**
     * Stocke les métriques pour historique
     */
    public function storeMetrics(): void
    {
        $timestamp = Carbon::now();
        
        // Stockage en base pour historique
        DB::table('metrics_history')->insert([
            'timestamp' => $timestamp,
            'metrics' => json_encode($this->metrics),
            'created_at' => $timestamp,
        ]);
        
        // Stockage en cache pour accès rapide
        Cache::put('latest_metrics', $this->metrics, now()->addMinutes(5));
        
        // Nettoyage de l'historique (garde 30 jours)
        DB::table('metrics_history')
            ->where('timestamp', '<', Carbon::now()->subDays(30))
            ->delete();
    }
    
    /**
     * Récupère l'historique des métriques
     */
    public function getMetricsHistory(string $period = '24h'): Collection
    {
        $since = match($period) {
            '1h' => Carbon::now()->subHour(),
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subWeek(),
            '30d' => Carbon::now()->subMonth(),
            default => Carbon::now()->subDay(),
        };
        
        return DB::table('metrics_history')
            ->where('timestamp', '>=', $since)
            ->orderBy('timestamp')
            ->get()
            ->map(function ($record) {
                $record->metrics = json_decode($record->metrics, true);
                return $record;
            });
    }
    
    // Méthodes privées pour calculer les métriques
    
    private function getMemoryUsage(): float
    {
        $memory = memory_get_usage(true);
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') return 0; // Unlimited
        
        $limit = $this->parseBytes($limit);
        return round(($memory / $limit) * 100, 2);
    }
    
    private function getCpuUsage(): float
    {
        // Simulation - en réalité nécessite des outils système
        return rand(10, 80);
    }
    
    private function getDiskUsage(): float
    {
        $total = disk_total_space('.');
        $free = disk_free_space('.');
        
        if (!$total || !$free) return 0;
        
        return round(((($total - $free) / $total) * 100), 2);
    }
    
    private function getDatabaseConnections(): int
    {
        try {
            // Simulation pour SQLite - MySQL specific query not available
            return rand(5, 20);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getCacheHitRatio(): float
    {
        // Simulation - dépend du driver de cache
        return rand(85, 99);
    }
    
    private function getQueueSize(): int
    {
        return Job::where('status', 'pending')->count();
    }
    
    private function getUptime(): int
    {
        // Simulation - en réalité, stocké au démarrage
        return rand(86400, 604800); // 1-7 jours en secondes
    }
    
    private function getActiveUsers24h(): int
    {
        // Simulation - en réalité basé sur les sessions/activité
        $recentMovies = Movie::where('created_at', '>=', Carbon::now()->subDay())
                            ->pluck('user_id')
                            ->unique();
        
        $recentFestivals = Festival::where('created_at', '>=', Carbon::now()->subDay())
                                 ->pluck('user_id')
                                 ->unique();
        
        return $recentMovies->merge($recentFestivals)->unique()->count();
    }
    
    private function getDcpProcessingRate(): float
    {
        $total = Movie::count();
        if ($total === 0) return 0;
        
        $withDcp = Movie::whereNotNull('dcp_path')->count();
        return round(($withDcp / $total) * 100, 2);
    }
    
    private function getAverageJobDuration(): float
    {
        $avgSeconds = Job::where('status', 'completed')
                        ->whereNotNull('started_at')
                        ->whereNotNull('finished_at')
                        ->where('created_at', '>=', Carbon::now()->subDay())
                        ->get()
                        ->map(function ($job) {
                            return $job->started_at->diffInSeconds($job->finished_at);
                        })
                        ->avg();
        
        return round($avgSeconds / 60, 2); // Minutes
    }
    
    private function getAverageResponseTime(): float
    {
        // Simulation - en réalité basé sur les logs APM
        return rand(200, 800);
    }
    
    private function getThroughput(): float
    {
        // Simulation - requêtes par seconde
        return rand(50, 200);
    }
    
    private function getErrorRate(): float
    {
        // Simulation - pourcentage d'erreurs
        return rand(0, 3);
    }
    
    private function getJobSuccessRate(): float
    {
        $completed = Job::where('status', 'completed')
                       ->where('created_at', '>=', Carbon::now()->subDay())
                       ->count();
        
        $failed = Job::where('status', 'failed')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->count();
        
        $total = $completed + $failed;
        
        if ($total === 0) return 100;
        
        return round(($completed / $total) * 100, 2);
    }
    
    private function getApiRequests24h(): int
    {
        // Simulation - en réalité basé sur les logs
        return rand(5000, 25000);
    }
    
    private function getSlowQueriesCount(): int
    {
        // Simulation - requêtes lentes
        return rand(0, 10);
    }
    
    private function getCachePerformance(): array
    {
        return [
            'hits' => rand(8000, 12000),
            'misses' => rand(500, 1500),
            'hit_ratio' => rand(85, 95),
        ];
    }
    
    private function getDcpVolumeProcessed(): float
    {
        // Volume en GB traité dans les dernières 24h
        $processedCount = Job::where('type', 'dcp_processing')
                            ->where('status', 'completed')
                            ->where('created_at', '>=', Carbon::now()->subDay())
                            ->count();
        
        return $processedCount * 2.5; // Moyenne 2.5GB par DCP
    }
    
    private function getFestivalSubmissions(): array
    {
        $today = Carbon::now()->subDay();
        
        return [
            'new_submissions' => Movie::where('created_at', '>=', $today)->count(),
            'pending_review' => Movie::where('status', 'pending')->count(),
            'approved' => Movie::where('status', 'approved')
                              ->where('updated_at', '>=', $today)
                              ->count(),
        ];
    }
    
    private function getUserEngagement(): array
    {
        return [
            'daily_active' => $this->getActiveUsers24h(),
            'avg_session_duration' => rand(15, 45), // Minutes
            'feature_usage' => [
                'movie_upload' => rand(50, 200),
                'dcp_validation' => rand(30, 120),
                'festival_submission' => rand(20, 80),
            ],
        ];
    }
    
    private function getStorageEfficiency(): array
    {
        $totalFiles = Movie::whereNotNull('dcp_path')->count();
        $duplicates = 0; // Simulation - détection de doublons
        
        return [
            'total_files' => $totalFiles,
            'duplicates' => $duplicates,
            'compression_ratio' => rand(75, 90), // Pourcentage
            'storage_optimization' => rand(60, 85),
        ];
    }
    
    private function getProcessingEfficiency(): array
    {
        return [
            'avg_processing_time' => $this->getAverageJobDuration(),
            'queue_efficiency' => rand(85, 98),
            'resource_utilization' => rand(70, 90),
            'batch_success_rate' => rand(90, 99),
        ];
    }
    
    private function getRevenueMetrics(): array
    {
        // Simulation - métriques business si applicable
        return [
            'monthly_revenue' => rand(5000, 15000),
            'subscription_growth' => rand(-5, 15), // Pourcentage
            'churn_rate' => rand(2, 8), // Pourcentage
            'arpu' => rand(50, 150), // Average Revenue Per User
        ];
    }
    
    private function parseBytes(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;
        
        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
