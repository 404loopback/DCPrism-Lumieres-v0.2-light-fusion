<?php

namespace App\Services;

use App\Models\DcprismNotification;
use App\Models\User;
use App\Models\Festival;
use App\Models\Movie;
use Illuminate\Support\Facades\Auth;

/**
 * Service pour créer et tester les notifications DCPrism
 */
class NotificationTestService
{
    /**
     * Créer une notification de test pour l'utilisateur connecté
     */
    public function createTestNotification(): ?DcprismNotification
    {
        if (!Auth::check()) {
            return null;
        }

        return DcprismNotification::createForUser(Auth::id(), [
            'type' => 'movie_uploaded',
            'title' => 'Test - Film uploadé',
            'message' => 'Votre film "Test Movie" a été uploadé avec succès et est en cours d\'analyse.',
            'action_url' => '/manager/movies',
            'action_label' => 'Voir le film',
            'is_important' => false
        ]);
    }

    /**
     * Créer plusieurs notifications de test
     */
    public function createMultipleTestNotifications(int $count = 5): array
    {
        if (!Auth::check()) {
            return [];
        }

        $notifications = [];
        $testData = [
            [
                'type' => 'movie_validated',
                'title' => 'Film validé',
                'message' => 'Votre film "Le Fabuleux Destin" a été validé par l\'équipe technique.',
                'action_url' => '/manager/movies/1',
                'action_label' => 'Voir le film'
            ],
            [
                'type' => 'dcp_ready', 
                'title' => 'DCP prêt',
                'message' => 'Le DCP pour "Amélie Poulain" est maintenant disponible au téléchargement.',
                'action_url' => '/manager/dcps/1',
                'action_label' => 'Télécharger'
            ],
            [
                'type' => 'festival_deadline',
                'title' => 'Deadline approche',
                'message' => 'Plus que 3 jours pour soumettre vos films au Festival de Cannes 2024.',
                'action_url' => '/manager/festivals/1',
                'action_label' => 'Voir le festival',
                'is_important' => true
            ],
            [
                'type' => 'comment_received',
                'title' => 'Nouveau commentaire',
                'message' => 'Un commentaire a été ajouté sur votre film "Intouchables".',
                'action_url' => '/manager/movies/2',
                'action_label' => 'Lire le commentaire'
            ],
            [
                'type' => 'festival_assigned',
                'title' => 'Assigné au festival',
                'message' => 'Vous avez été assigné au Festival International du Film de Berlin.',
                'action_url' => '/manager/festivals/2', 
                'action_label' => 'Accéder au festival'
            ],
            [
                'type' => 'movie_rejected',
                'title' => 'Film rejeté',
                'message' => 'Votre film "Test Film" a été rejeté. Raison: Format non conforme.',
                'action_url' => '/manager/movies/3',
                'action_label' => 'Voir les détails'
            ],
            [
                'type' => 'dcp_failed',
                'title' => 'Erreur DCP', 
                'message' => 'La génération du DCP pour "Mon Film" a échoué. Veuillez vérifier le fichier source.',
                'action_url' => '/manager/dcps/2',
                'action_label' => 'Voir l\'erreur'
            ],
            [
                'type' => 'system_maintenance',
                'title' => 'Maintenance programmée',
                'message' => 'Une maintenance du système est programmée demain de 02h00 à 04h00 CET.',
                'is_important' => true
            ]
        ];

        for ($i = 0; $i < min($count, count($testData)); $i++) {
            $data = $testData[$i];
            
            // Ajouter un délai pour simuler différents moments
            $createdAt = now()->subMinutes(rand(1, 60 * 24)); // Entre 1 minute et 24h
            
            $notification = DcprismNotification::createForUser(Auth::id(), $data);
            $notification->created_at = $createdAt;
            $notification->save();
            
            // Marquer quelques notifications comme lues
            if (rand(0, 100) < 30) { // 30% de chance
                $notification->markAsRead();
            }
            
            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Créer une notification importante
     */
    public function createImportantNotification(): ?DcprismNotification
    {
        if (!Auth::check()) {
            return null;
        }

        return DcprismNotification::createForUser(Auth::id(), [
            'type' => 'festival_deadline',
            'title' => '🚨 URGENT - Deadline dans 24h',
            'message' => 'La deadline pour le Festival de Cannes 2024 est dans 24 heures ! N\'oubliez pas de soumettre vos films.',
            'action_url' => '/manager/festivals/1',
            'action_label' => 'Soumettre maintenant',
            'is_important' => true
        ]);
    }

    /**
     * Créer une notification avec contexte festival
     */
    public function createFestivalNotification(int $festivalId): ?DcprismNotification
    {
        if (!Auth::check()) {
            return null;
        }

        return DcprismNotification::createForUser(Auth::id(), [
            'type' => 'festival_assigned',
            'title' => 'Nouveau festival assigné',
            'message' => 'Vous avez été assigné à un nouveau festival et pouvez maintenant gérer ses films.',
            'festival_id' => $festivalId,
            'action_url' => "/manager/festivals/{$festivalId}",
            'action_label' => 'Accéder au festival'
        ]);
    }

    /**
     * Créer une notification liée à un film
     */
    public function createMovieNotification(int $movieId, string $type = 'movie_uploaded'): ?DcprismNotification
    {
        if (!Auth::check()) {
            return null;
        }

        $messages = [
            'movie_uploaded' => 'a été uploadé avec succès',
            'movie_validated' => 'a été validé par l\'équipe technique', 
            'movie_rejected' => 'a été rejeté. Veuillez vérifier les critères.',
            'dcp_ready' => 'DCP prêt pour téléchargement',
            'dcp_failed' => 'Échec de génération du DCP'
        ];

        return DcprismNotification::createForUser(Auth::id(), [
            'type' => $type,
            'title' => ucfirst(str_replace('_', ' ', $type)),
            'message' => "Votre film {$messages[$type]}",
            'movie_id' => $movieId,
            'action_url' => "/manager/movies/{$movieId}",
            'action_label' => 'Voir le film'
        ]);
    }

    /**
     * Nettoyer toutes les notifications de test
     */
    public function clearTestNotifications(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return DcprismNotification::where('user_id', Auth::id())
            ->where('title', 'like', 'Test%')
            ->orWhere('title', 'like', '%test%')
            ->delete();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return DcprismNotification::markAllAsReadForUser(Auth::id());
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getNotificationStats(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $userId = Auth::id();
        $total = DcprismNotification::forUser($userId)->count();
        $unread = DcprismNotification::forUser($userId)->unread()->count();
        $important = DcprismNotification::forUser($userId)->important()->count();
        $today = DcprismNotification::forUser($userId)->whereDate('created_at', today())->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'important' => $important,
            'today' => $today,
            'read_rate' => $total > 0 ? round((($total - $unread) / $total) * 100, 1) : 0
        ];
    }
}
