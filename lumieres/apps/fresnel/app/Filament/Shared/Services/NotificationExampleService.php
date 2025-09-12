<?php

namespace App\Filament\Shared\Services;

use App\Filament\Shared\Concerns\HasNotifications;
use App\Models\User;
use App\Models\Festival;
use App\Notifications\TestNotification;
use App\Notifications\JobCompletedNotification;
use App\Notifications\JobFailedNotification;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

/**
 * Service d'exemple pour démontrer l'utilisation du système de notifications
 * 
 * Cette classe montre comment utiliser les notifications Filament natives
 * avec l'architecture Shared de DCPrism
 */
class NotificationExampleService
{
    use HasNotifications;

    /**
     * Exemple : Envoyer des notifications simples
     */
    public function sendSimpleNotifications(): void
    {
        // Notifications basiques
        $this->notifySuccess('Opération réussie', 'Votre action a été exécutée avec succès');
        $this->notifyInfo('Information', 'Voici une information importante');
        $this->notifyWarning('Attention', 'Vérifiez cette configuration');
        $this->notifyError('Erreur', 'Une erreur est survenue');
    }

    /**
     * Exemple : Notifications de jobs
     */
    public function sendJobNotifications(): void
    {
        // Notification de job terminé
        $this->notifyJobCompleted(
            'Analyse DCP', 
            'Le fichier a été analysé avec succès',
            '/admin/movies/123'
        );

        // Notification de job échoué
        $this->notifyJobFailed(
            'Validation DCP',
            'Fichier corrrompu détecté',
            '/admin/movies/123'
        );
    }

    /**
     * Exemple : Notifications métier
     */
    public function sendBusinessNotifications(): void
    {
        // Film uploadé
        $this->notifyMovieUploaded('Le Fabuleux Destin d\'Amélie Poulain', '/admin/movies/456');

        // DCP prêt
        $this->notifyDcpReady('Intouchables', '/admin/dcps/789');

        // Annonce festival
        $this->notifyFestivalAnnouncement(
            'Nouvelle deadline',
            'La date limite de soumission a été reportée au 15 octobre',
            '/admin/festivals/1'
        );
    }

    /**
     * Exemple : Notification avec contexte festival
     */
    public function sendFestivalContextNotification(): void
    {
        $this->sendFestivalNotification(
            'Nouveau film soumis',
            'Un nouveau film a été soumis pour validation',
            'info',
            '/manager/movies'
        );
    }

    /**
     * Exemple : Notifications persistantes (stockées en base)
     */
    public function sendPersistentNotifications(): void
    {
        $user = User::find(1); // Exemple

        if ($user) {
            $this->sendPersistentNotification(
                'Mise à jour importante',
                'Votre profil doit être mis à jour avant le 30 novembre',
                'warning',
                $user
            );
        }
    }

    /**
     * Exemple : Notifications par rôle
     */
    public function sendRoleBasedNotifications(): void
    {
        // Notifier tous les admins
        $this->notifyAdmins(
            'Maintenance programmée',
            'Le système sera en maintenance demain de 02h00 à 04h00'
        );

        // Notifier l'équipe technique
        $this->notifyTechnicalTeam(
            'Problème détecté',
            'Un problème de performance a été détecté sur le serveur de stockage'
        );

        // Notifier par rôle spécifique
        $this->notifyUsersByRole(
            'manager',
            'Nouveau rapport disponible',
            'Le rapport mensuel est maintenant disponible dans la section rapports'
        );
    }

    /**
     * Exemple : Notification personnalisée complète
     */
    public function sendAdvancedNotification(): void
    {
        $this->sendCustomNotification(
            'Action requise',
            'Plusieurs films nécessitent votre validation avant la date limite',
            'heroicon-o-exclamation-triangle',
            'warning',
            [
                Action::make('validate_all')
                    ->label('Valider tout')
                    ->button()
                    ->color('success'),
                Action::make('view_list')
                    ->label('Voir la liste')
                    ->button()
                    ->outlined()
            ]
        );
    }

    /**
     * Exemple : Notification Laravel traditionnelle via database
     */
    public function sendTraditionalNotification(): void
    {
        $user = User::find(1);

        if ($user) {
            $user->notify(new TestNotification([
                'title' => 'Test du système',
                'message' => 'Le système de notifications fonctionne parfaitement'
            ]));
        }
    }

    /**
     * Exemple : Notification en lot pour plusieurs utilisateurs
     */
    public function sendBulkNotifications(): void
    {
        // Récupérer les utilisateurs d'un festival
        $festival = Festival::find(1);
        
        if ($festival) {
            $managers = $festival->users()
                ->where('role', 'manager')
                ->where('is_active', true)
                ->get();

            foreach ($managers as $manager) {
                $manager->notify(new JobCompletedNotification([
                    'movie' => (object) ['id' => 123, 'title' => 'Film de test'],
                    'job_type' => 'validation',
                    'data' => []
                ]));
            }
        }
    }

    /**
     * Exemple : Notification avec toast Filament direct
     */
    public function sendFilamentToast(): void
    {
        Notification::make()
            ->title('Toast de test')
            ->body('Ceci est un toast Filament directe')
            ->icon('heroicon-o-sparkles')
            ->color('success')
            ->duration(5000)
            ->actions([
                Action::make('dismiss')
                    ->label('Fermer')
                    ->close(),
                Action::make('learn_more')
                    ->label('En savoir plus')
                    ->url('https://filamentphp.com/docs/notifications')
                    ->openUrlInNewTab()
            ])
            ->send();
    }

    /**
     * Exemple : Notification conditionnelle basée sur les permissions
     */
    public function sendConditionalNotification(): void
    {
        // Vérifier les permissions avant d'envoyer
        if ($this->hasManagementAccess()) {
            $this->notifyWarning(
                'Accès management détecté',
                'Vous avez accès aux fonctions de gestion'
            );
        }

        if ($this->hasTechAccess()) {
            $this->notifyInfo(
                'Outils techniques disponibles',
                'Vous pouvez accéder aux outils de diagnostic'
            );
        }

        if ($this->isAdmin()) {
            $this->notifySuccess(
                'Accès administrateur',
                'Toutes les fonctionnalités sont disponibles'
            );
        }
    }

    /**
     * Démonstration complète du système
     */
    public function demonstrateNotificationSystem(): void
    {
        $this->sendSimpleNotifications();
        $this->sendJobNotifications();
        $this->sendBusinessNotifications();
        $this->sendFestivalContextNotification();
        $this->sendPersistentNotifications();
        $this->sendRoleBasedNotifications();
        $this->sendAdvancedNotification();
        $this->sendTraditionalNotification();
        $this->sendFilamentToast();
        $this->sendConditionalNotification();

        // Notification finale
        $this->notifySuccess(
            'Démonstration terminée',
            'Tous les types de notifications ont été testés avec succès'
        );
    }
}
