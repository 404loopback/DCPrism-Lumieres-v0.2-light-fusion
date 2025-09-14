<?php

namespace Modules\Fresnel\app\Filament\Shared\Widgets;

use Modules\Fresnel\app\Filament\Shared\Concerns\HasFestivalContext;
use Modules\Fresnel\app\Filament\Shared\Concerns\HasRoleBasedAccess;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Widget de centre de notifications utilisant les composants Filament natifs
 */
class NotificationCenterWidget extends Widget
{
    use HasFestivalContext, HasRoleBasedAccess;

    protected static string $view = 'filament.shared.widgets.notification-center';

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Centre de notifications';
    }

    /**
     * Vérifier si le widget peut être affiché
     */
    public static function canView(): bool
    {
        return Auth::check();
    }

    /**
     * Obtenir les données pour le widget
     */
    protected function getViewData(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [
                'notifications' => collect(),
                'unread_count' => 0,
                'stats' => []
            ];
        }

        // Récupérer les notifications récentes
        $notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();
        
        // Statistiques des notifications
        $stats = $this->getNotificationStats($user);
        
        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'stats' => $stats,
            'user' => $user,
            'festival_context' => $this->getFestivalContext()
        ];
    }

    /**
     * Obtenir les statistiques des notifications
     */
    private function getNotificationStats($user): array
    {
        $total = $user->notifications()->count();
        $unread = $user->unreadNotifications()->count();
        $today = $user->notifications()->whereDate('created_at', today())->count();
        $thisWeek = $user->notifications()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'today' => $today,
            'this_week' => $thisWeek,
            'read_rate' => $total > 0 ? round((($total - $unread) / $total) * 100, 1) : 0
        ];
    }

    /**
     * Actions pour le widget
     */
    public function markAllAsRead(): void
    {
        $user = Auth::user();
        
        if ($user) {
            $user->unreadNotifications()->update(['read_at' => now()]);
            
            // Notification de succès
            \Filament\Notifications\Notification::make()
                ->title('Notifications marquées comme lues')
                ->success()
                ->send();
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(string $notificationId): void
    {
        $user = Auth::user();
        
        if ($user) {
            $notification = $user->notifications()->find($notificationId);
            
            if ($notification && !$notification->read_at) {
                $notification->markAsRead();
            }
        }
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification(string $notificationId): void
    {
        $user = Auth::user();
        
        if ($user) {
            $notification = $user->notifications()->find($notificationId);
            
            if ($notification) {
                $notification->delete();
                
                \Filament\Notifications\Notification::make()
                    ->title('Notification supprimée')
                    ->success()
                    ->send();
            }
        }
    }

    /**
     * Vider toutes les notifications lues
     */
    public function clearReadNotifications(): void
    {
        $user = Auth::user();
        
        if ($user) {
            $count = $user->readNotifications()->count();
            $user->readNotifications()->delete();
            
            \Filament\Notifications\Notification::make()
                ->title("$count notifications supprimées")
                ->success()
                ->send();
        }
    }
}
