<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class AuditService
{
    /**
     * Log une authentification utilisateur
     */
    public function logAuthentication(int $userId, string $event, array $properties = []): Activity
    {
        return activity('authentication')
            ->performedOn(new \App\Models\User(['id' => $userId]))
            ->causedBy($userId)
            ->withProperties([
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'event' => $event,
                ...$properties
            ])
            ->log(match($event) {
                'login' => 'Connexion utilisateur',
                'logout' => 'Déconnexion utilisateur',
                'failed_login' => 'Tentative de connexion échouée',
                'password_reset' => 'Réinitialisation du mot de passe',
                'email_verification' => 'Vérification de l\'email',
                default => 'Événement d\'authentification'
            });
    }

    /**
     * Log une action d'administration
     */
    public function logAdminAction(string $action, $subject = null, array $properties = []): Activity
    {
        return activity('admin')
            ->performedOn($subject)
            ->causedBy(Auth::id())
            ->withProperties([
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'action' => $action,
                ...$properties
            ])
            ->log("Action administrateur: {$action}");
    }

    /**
     * Log un événement de traitement DCP
     */
    public function logDcpEvent(string $event, $movie = null, array $properties = []): Activity
    {
        return activity('dcp_processing')
            ->performedOn($movie)
            ->causedBy(Auth::id())
            ->withProperties([
                'event' => $event,
                'timestamp' => now()->toISOString(),
                ...$properties
            ])
            ->log(match($event) {
                'job_created' => 'Tâche DCP créée',
                'job_started' => 'Traitement DCP démarré',
                'job_completed' => 'Traitement DCP terminé',
                'job_failed' => 'Échec du traitement DCP',
                'job_cancelled' => 'Traitement DCP annulé',
                'validation_started' => 'Validation technique démarrée',
                'validation_completed' => 'Validation technique terminée',
                'validation_rejected' => 'Validation technique rejetée',
                default => "Événement DCP: {$event}"
            });
    }

    /**
     * Log un événement système
     */
    public function logSystemEvent(string $event, array $properties = []): Activity
    {
        return activity('system')
            ->withProperties([
                'event' => $event,
                'timestamp' => now()->toISOString(),
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true),
                ],
                ...$properties
            ])
            ->log("Événement système: {$event}");
    }

    /**
     * Log un événement de sécurité
     */
    public function logSecurityEvent(string $event, array $properties = []): Activity
    {
        return activity('security')
            ->causedBy(Auth::id())
            ->withProperties([
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'event' => $event,
                'timestamp' => now()->toISOString(),
                ...$properties
            ])
            ->log("Événement de sécurité: {$event}");
    }

    /**
     * Nettoyer les logs anciens selon les règles GDPR
     */
    public function cleanupOldLogs(int $retentionDays = 730): int
    {
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $deletedCount = Activity::where('created_at', '<', $cutoffDate)->delete();
        
        $this->logSystemEvent('audit_cleanup', [
            'retention_days' => $retentionDays,
            'deleted_logs_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toDateString()
        ]);

        return $deletedCount;
    }

    /**
     * Anonymiser les données utilisateur dans les logs pour compliance GDPR
     */
    public function anonymizeUserLogs(int $userId): int
    {
        $updatedCount = Activity::where('causer_id', $userId)
            ->update([
                'causer_id' => null,
                'causer_type' => null,
                'properties' => \DB::raw("JSON_SET(properties, '$.anonymized', true, '$.user_id', 'ANONYMIZED')")
            ]);

        $this->logSystemEvent('user_data_anonymization', [
            'original_user_id' => $userId,
            'updated_logs_count' => $updatedCount,
            'anonymization_date' => now()->toISOString()
        ]);

        return $updatedCount;
    }

    /**
     * Exporter les logs d'un utilisateur pour compliance GDPR
     */
    public function exportUserLogs(int $userId): array
    {
        $activities = Activity::where('causer_id', $userId)
            ->orWhere('subject_id', $userId)
            ->orWhereJsonContains('properties->user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $export = [
            'export_date' => now()->toISOString(),
            'user_id' => $userId,
            'total_activities' => $activities->count(),
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'causer_type' => $activity->causer_type,
                    'causer_id' => $activity->causer_id,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->toISOString(),
                ];
            })->toArray()
        ];

        $this->logSystemEvent('user_data_export', [
            'user_id' => $userId,
            'exported_logs_count' => $activities->count()
        ]);

        return $export;
    }

    /**
     * Obtenir des statistiques d'audit
     */
    public function getAuditStats(): array
    {
        return [
            'total_activities' => Activity::count(),
            'activities_by_log_name' => Activity::select('log_name', \DB::raw('count(*) as count'))
                ->groupBy('log_name')
                ->get()
                ->pluck('count', 'log_name')
                ->toArray(),
            'activities_last_7_days' => Activity::where('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
            'activities_last_30_days' => Activity::where('created_at', '>=', Carbon::now()->subDays(30))
                ->count(),
            'unique_users_last_30_days' => Activity::where('created_at', '>=', Carbon::now()->subDays(30))
                ->distinct('causer_id')
                ->whereNotNull('causer_id')
                ->count(),
            'oldest_activity' => Activity::min('created_at'),
            'newest_activity' => Activity::max('created_at'),
        ];
    }
}
