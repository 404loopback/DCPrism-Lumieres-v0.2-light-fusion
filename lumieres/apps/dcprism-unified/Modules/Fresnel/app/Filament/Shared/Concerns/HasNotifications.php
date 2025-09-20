<?php

namespace Modules\Fresnel\app\Filament\Shared\Concerns;

use Modules\Fresnel\app\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Modules\Fresnel\app\Models\Festival;

/**
 * Trait pour gérer les notifications Filament natives
 *
 * Utilise le système de notifications intégré de Filament 4.0.4
 * avec support des contextes de festival et des rôles utilisateur
 */
trait HasNotifications
{
    use HasFestivalContext;

    /**
     * Types de notifications prédéfinis avec leurs configurations
     */
    protected function getNotificationTypes(): array
    {
        return [
            'success' => [
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
            ],
            'warning' => [
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'warning',
            ],
            'error' => [
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
            ],
            'info' => [
                'icon' => 'heroicon-o-information-circle',
                'color' => 'info',
            ],
            'job_completed' => [
                'icon' => 'heroicon-o-check-badge',
                'color' => 'success',
            ],
            'job_failed' => [
                'icon' => 'heroicon-o-exclamation-circle',
                'color' => 'danger',
            ],
            'movie_uploaded' => [
                'icon' => 'heroicon-o-film',
                'color' => 'info',
            ],
            'dcp_ready' => [
                'icon' => 'heroicon-o-play',
                'color' => 'success',
            ],
            'festival_announcement' => [
                'icon' => 'heroicon-o-megaphone',
                'color' => 'warning',
            ],
        ];
    }

    /**
     * Envoyer une notification de succès
     */
    protected function notifySuccess(string $title, ?string $body = null): void
    {
        $this->sendNotification('success', $title, $body);
    }

    /**
     * Envoyer une notification d'avertissement
     */
    protected function notifyWarning(string $title, ?string $body = null): void
    {
        $this->sendNotification('warning', $title, $body);
    }

    /**
     * Envoyer une notification d'erreur
     */
    protected function notifyError(string $title, ?string $body = null): void
    {
        $this->sendNotification('error', $title, $body);
    }

    /**
     * Envoyer une notification d'information
     */
    protected function notifyInfo(string $title, ?string $body = null): void
    {
        $this->sendNotification('info', $title, $body);
    }

    /**
     * Notification pour job terminé avec succès
     */
    protected function notifyJobCompleted(string $jobName, ?string $details = null, ?string $actionUrl = null): void
    {
        $notification = $this->buildNotification('job_completed', "Job terminé : {$jobName}", $details);

        if ($actionUrl) {
            $notification->actions([
                Action::make('view')
                    ->label('Voir les détails')
                    ->url($actionUrl)
                    ->button(),
            ]);
        }

        $notification->send();
    }

    /**
     * Notification pour job échoué
     */
    protected function notifyJobFailed(string $jobName, ?string $error = null, ?string $actionUrl = null): void
    {
        $body = $error ? "Erreur : {$error}" : null;
        $notification = $this->buildNotification('job_failed', "Job échoué : {$jobName}", $body);

        if ($actionUrl) {
            $notification->actions([
                Action::make('retry')
                    ->label('Relancer')
                    ->url($actionUrl)
                    ->button()
                    ->color('warning'),
            ]);
        }

        $notification->send();
    }

    /**
     * Notification pour nouveau film uploadé
     */
    protected function notifyMovieUploaded(string $movieTitle, ?string $actionUrl = null): void
    {
        $notification = $this->buildNotification(
            'movie_uploaded',
            'Nouveau film uploadé',
            "Le film \"{$movieTitle}\" a été téléchargé avec succès."
        );

        if ($actionUrl) {
            $notification->actions([
                Action::make('view')
                    ->label('Voir le film')
                    ->url($actionUrl)
                    ->button(),
            ]);
        }

        $notification->send();
    }

    /**
     * Notification pour DCP prêt
     */
    protected function notifyDcpReady(string $movieTitle, ?string $actionUrl = null): void
    {
        $notification = $this->buildNotification(
            'dcp_ready',
            'DCP prêt pour distribution',
            "Le DCP pour \"{$movieTitle}\" est maintenant disponible."
        );

        if ($actionUrl) {
            $notification->actions([
                Action::make('download')
                    ->label('Télécharger')
                    ->url($actionUrl)
                    ->button()
                    ->color('success'),
            ]);
        }

        $notification->send();
    }

    /**
     * Notification d'annonce festival
     */
    protected function notifyFestivalAnnouncement(string $title, string $message, ?string $actionUrl = null): void
    {
        $notification = $this->buildNotification('festival_announcement', $title, $message);

        if ($actionUrl) {
            $notification->actions([
                Action::make('read_more')
                    ->label('En savoir plus')
                    ->url($actionUrl)
                    ->button(),
            ]);
        }

        $notification->send();
    }

    /**
     * Envoyer une notification personnalisée
     */
    protected function sendCustomNotification(
        string $title,
        ?string $body = null,
        ?string $icon = null,
        ?string $color = null,
        array $actions = []
    ): void {
        $notification = Notification::make()
            ->title($title)
            ->icon($icon ?? 'heroicon-o-bell')
            ->color($color ?? 'info');

        if ($body) {
            $notification->body($body);
        }

        if (! empty($actions)) {
            $notification->actions($actions);
        }

        $notification->send();
    }

    /**
     * Envoyer une notification basée sur le contexte festival
     */
    protected function sendFestivalNotification(
        string $title,
        ?string $body = null,
        ?string $type = 'info',
        ?string $actionUrl = null
    ): void {
        $festivalContext = $this->getFestivalContext();
        $festivalName = $festivalContext['name'] ?? 'Festival';

        $fullTitle = "[{$festivalName}] {$title}";

        $notification = $this->buildNotification($type, $fullTitle, $body);

        if ($actionUrl) {
            $notification->actions([
                Action::make('view')
                    ->label('Voir dans le festival')
                    ->url($actionUrl)
                    ->button(),
            ]);
        }

        $notification->send();
    }

    /**
     * Notification persistante (stockée en base)
     */
    protected function sendPersistentNotification(
        string $title,
        ?string $body = null,
        ?string $type = 'info',
        ?User $recipient = null
    ): void {
        $recipient = $recipient ?? auth()->user();

        if (! $recipient) {
            return;
        }

        $notification = $this->buildNotification($type, $title, $body);

        // Stocker en base de données
        $notification->sendToDatabase($recipient);
    }

    /**
     * Notification pour tous les utilisateurs d'un rôle (Shield)
     */
    protected function notifyUsersByRole(
        string $role,
        string $title,
        ?string $body = null,
        ?string $type = 'info',
        ?Festival $festival = null
    ): void {
        // Utiliser Shield pour trouver les utilisateurs par rôle
        $query = User::whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        })->where('is_active', true);

        if ($festival) {
            $query->whereHas('festivals', function ($q) use ($festival) {
                $q->where('festivals.id', $festival->id);
            });
        }

        $users = $query->get();
        $notification = $this->buildNotification($type, $title, $body);

        foreach ($users as $user) {
            $notification->sendToDatabase($user);
        }
    }

    /**
     * Notification pour tous les administrateurs
     */
    protected function notifyAdmins(string $title, ?string $body = null, ?string $type = 'warning'): void
    {
        $this->notifyUsersByRole('admin', $title, $body, $type);
    }

    /**
     * Notification pour l'équipe technique
     */
    protected function notifyTechnicalTeam(string $title, ?string $body = null, ?Festival $festival = null): void
    {
        $this->notifyUsersByRole('tech', $title, $body, 'info', $festival);
        $this->notifyUsersByRole('admin', $title, $body, 'info', $festival);
    }

    /**
     * Construire une notification avec les paramètres prédéfinis
     */
    private function buildNotification(string $type, string $title, ?string $body = null): Notification
    {
        $types = $this->getNotificationTypes();
        $config = $types[$type] ?? $types['info'];

        $notification = Notification::make()
            ->title($title)
            ->icon($config['icon'])
            ->color($config['color']);

        if ($body) {
            $notification->body($body);
        }

        return $notification;
    }

    /**
     * Envoyer une notification de base
     */
    private function sendNotification(string $type, string $title, ?string $body = null): void
    {
        $this->buildNotification($type, $title, $body)->send();
    }

    /**
     * Obtenir le nombre de notifications non lues pour l'utilisateur actuel
     */
    protected function getUnreadNotificationsCount(): int
    {
        $user = auth()->user();

        if (! $user) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    protected function markAllNotificationsAsRead(): void
    {
        $user = auth()->user();

        if ($user) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }
    }
}
