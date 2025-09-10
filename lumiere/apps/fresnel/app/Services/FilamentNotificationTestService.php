<?php

namespace App\Services;

use App\Notifications\DcpMovieUploadedNotification;
use App\Notifications\DcpReadyNotification;
use App\Notifications\FestivalDeadlineNotification;
use App\Models\User;
use App\Models\Festival;
use App\Models\Movie;
use App\Models\Dcp;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

/**
 * Service pour tester le système de notifications Filament natif
 */
class FilamentNotificationTestService
{
    /**
     * Créer des notifications de test variées
     */
    public function createTestNotifications(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $notifications = [];

        // 1. Toast simple
        $notifications[] = FilamentNotification::make()
            ->title('🧪 Test réussi !')
            ->body('Le système de notifications Filament fonctionne parfaitement')
            ->icon('heroicon-o-beaker')
            ->color('success')
            ->send();

        // 2. Notification persistante
        $notifications[] = FilamentNotification::make()
            ->title('📥 Notification persistante')
            ->body('Cette notification reste dans votre historique')
            ->icon('heroicon-o-archive-box')
            ->color('info')
            ->actions([
                Action::make('test')
                    ->label('Action test')
                    ->button()
                    ->color('primary')
            ])
            ->sendToDatabase($user);

        // 3. Notification importante
        $notifications[] = FilamentNotification::make()
            ->title('🚨 Notification importante')
            ->body('Ceci est une notification prioritaire qui nécessite votre attention')
            ->icon('heroicon-o-exclamation-triangle')
            ->color('warning')
            ->actions([
                Action::make('acknowledge')
                    ->label('Pris en compte')
                    ->button()
                    ->color('success'),
                Action::make('dismiss')
                    ->label('Ignorer')
                    ->button()
                    ->outlined()
            ])
            ->sendToDatabase($user);

        // 4. Notification d'erreur
        $notifications[] = FilamentNotification::make()
            ->title('❌ Erreur simulée')
            ->body('Une erreur de test pour démontrer les notifications d\'erreur')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->actions([
                Action::make('retry')
                    ->label('Réessayer')
                    ->button()
                    ->color('danger'),
                Action::make('details')
                    ->label('Voir détails')
                    ->button()
                    ->outlined()
            ])
            ->sendToDatabase($user);

        // 5. Notification métier - Film
        $notifications[] = FilamentNotification::make()
            ->title('🎬 Nouveau film')
            ->body('Le film "Test Movie" a été ajouté à votre collection')
            ->icon('heroicon-o-film')
            ->color('primary')
            ->actions([
                Action::make('view')
                    ->label('Voir le film')
                    ->url('/manager/movies')
                    ->button(),
                Action::make('edit')
                    ->label('Modifier')
                    ->url('/manager/movies')
                    ->button()
                    ->outlined()
            ])
            ->sendToDatabase($user);

        return $notifications;
    }

    /**
     * Créer une notification de film uploadé
     */
    public function createMovieUploadedNotification(): void
    {
        if (!Auth::check()) {
            return;
        }

        // Créer un objet movie factice pour le test
        $movie = new Movie([
            'id' => 999,
            'title' => 'Film de Test - ' . now()->format('H:i:s')
        ]);

        DcpMovieUploadedNotification::sendToUser(Auth::user(), $movie);
    }

    /**
     * Créer une notification DCP prêt
     */
    public function createDcpReadyNotification(): void
    {
        if (!Auth::check()) {
            return;
        }

        $movie = new Movie([
            'id' => 998,
            'title' => 'Amélie Poulain - Test DCP'
        ]);

        $dcp = new Dcp([
            'id' => 777,
            'movie_id' => 998
        ]);

        DcpReadyNotification::sendToUser(Auth::user(), $movie, $dcp);
    }

    /**
     * Créer une notification deadline festival
     */
    public function createFestivalDeadlineNotification(): void
    {
        if (!Auth::check()) {
            return;
        }

        $festival = new Festival([
            'id' => 555,
            'name' => 'Festival de Cannes 2024',
            'submission_deadline' => now()->addDays(2)
        ]);

        FestivalDeadlineNotification::sendToUser(Auth::user(), $festival, 2);
    }

    /**
     * Créer une série de notifications pour simuler l'activité
     */
    public function simulateActivityNotifications(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $notifications = [];

        // Simuler différents événements sur plusieurs heures
        $events = [
            ['time' => 5, 'title' => '📤 Film uploadé', 'body' => 'Le film "Intouchables" a été uploadé', 'color' => 'success'],
            ['time' => 15, 'title' => '⚙️ Analyse en cours', 'body' => 'Analyse technique du film en cours...', 'color' => 'info'],
            ['time' => 45, 'title' => '✅ Validation réussie', 'body' => 'Le film a passé toutes les validations', 'color' => 'success'],
            ['time' => 60, 'title' => '🔄 Génération DCP', 'body' => 'Génération du DCP en cours...', 'color' => 'warning'],
            ['time' => 90, 'title' => '▶️ DCP disponible', 'body' => 'Votre DCP est prêt au téléchargement', 'color' => 'success'],
        ];

        foreach ($events as $event) {
            $notification = FilamentNotification::make()
                ->title($event['title'])
                ->body($event['body'])
                ->color($event['color'])
                ->actions([
                    Action::make('view')
                        ->label('Voir détails')
                        ->button()
                        ->outlined()
                ])
                ->sendToDatabase($user);
                
            // Modifier la date de création pour simuler l'historique
            $user->notifications()->latest()->first()->update([
                'created_at' => now()->subMinutes($event['time'])
            ]);
            
            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Créer des notifications avec différents niveaux d'importance
     */
    public function createImportanceVariationsNotifications(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $notifications = [];

        // Notification normale
        $notifications[] = FilamentNotification::make()
            ->title('📋 Information')
            ->body('Mise à jour des conditions d\'utilisation disponible')
            ->color('gray')
            ->sendToDatabase($user);

        // Notification important
        $notifications[] = FilamentNotification::make()
            ->title('⚠️ Attention requise')
            ->body('Votre quota de stockage est à 80%')
            ->color('warning')
            ->actions([
                Action::make('manage')
                    ->label('Gérer le stockage')
                    ->button()
                    ->color('warning')
            ])
            ->sendToDatabase($user);

        // Notification critique
        $notifications[] = FilamentNotification::make()
            ->title('🚨 Action urgente')
            ->body('Votre compte expire dans 24 heures')
            ->color('danger')
            ->actions([
                Action::make('renew')
                    ->label('Renouveler maintenant')
                    ->button()
                    ->color('danger'),
                Action::make('contact')
                    ->label('Contacter le support')
                    ->button()
                    ->outlined()
            ])
            ->sendToDatabase($user);

        return $notifications;
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getNotificationStats(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $notifications = $user->notifications();
        
        $total = $notifications->count();
        $unread = $user->unreadNotifications()->count();
        $today = $notifications->whereDate('created_at', today())->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'today' => $today,
            'read_rate' => $total > 0 ? round((($total - $unread) / $total) * 100, 1) : 0
        ];
    }

    /**
     * Nettoyer les notifications de test
     */
    public function clearTestNotifications(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Auth::user()->notifications()
            ->where('data->title', 'like', '%Test%')
            ->orWhere('data->title', 'like', '%test%')
            ->orWhere('data->body', 'like', '%test%')
            ->delete();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->unreadNotifications->markAsRead();
    }
}
