<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use App\Models\Festival;
use Carbon\Carbon;

class FestivalDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Festival $festival,
        protected int $daysRemaining,
        protected array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $urgencyLevel = $this->getUrgencyLevel();
        
        return [
            'title' => "⏰ {$urgencyLevel['title']}",
            'body' => "Plus que {$this->daysRemaining} jour(s) pour soumettre vos films au {$this->festival->name} !",
            'icon' => 'heroicon-o-calendar',
            'color' => $urgencyLevel['color'],
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'submit',
                    'label' => 'Soumettre maintenant',
                    'url' => "/manager/festivals/{$this->festival->id}/submit",
                    'color' => 'warning',
                ],
                [
                    'name' => 'view',
                    'label' => 'Voir le festival',
                    'url' => "/manager/festivals/{$this->festival->id}",
                    'color' => 'primary',
                ],
            ],
            
            'dcprism_type' => 'festival_deadline',
            'festival_id' => $this->festival->id,
            'days_remaining' => $this->daysRemaining,
            'is_important' => $this->daysRemaining <= 3,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgencyLevel = $this->getUrgencyLevel();
        
        return (new MailMessage)
            ->subject("DCPrism - {$urgencyLevel['title']} : {$this->festival->name}")
            ->greeting('Attention !')
            ->line("La deadline pour {$this->festival->name} approche rapidement !")
            ->line("Il ne reste plus que {$this->daysRemaining} jour(s) pour soumettre vos films.")
            ->line("Date limite : " . $this->festival->submission_deadline->format('d/m/Y à H:i'))
            ->action('Soumettre maintenant', url("/manager/festivals/{$this->festival->id}/submit"))
            ->line('N\'attendez pas la dernière minute pour éviter tout problème technique.')
            ->salutation('L\'équipe DCPrism');
    }

    public static function sendToUser($user, Festival $festival, int $daysRemaining, array $data = []): void
    {
        $instance = new self($festival, $daysRemaining, $data);
        $urgencyLevel = $instance->getUrgencyLevel();
        
        FilamentNotification::make()
            ->title("⏰ {$urgencyLevel['title']}")
            ->body("Plus que {$daysRemaining} jour(s) pour {$festival->name}")
            ->icon('heroicon-o-calendar')
            ->color($urgencyLevel['color'])
            ->actions([
                Action::make('submit')
                    ->label('Soumettre maintenant')
                    ->url("/manager/festivals/{$festival->id}/submit")
                    ->button()
                    ->color('warning'),
                Action::make('view')
                    ->label('Voir le festival')
                    ->url("/manager/festivals/{$festival->id}")
                    ->button()
                    ->outlined()
            ])
            ->sendToDatabase($user);

        $user->notify($instance);
    }

    /**
     * Détermine le niveau d'urgence basé sur les jours restants
     */
    private function getUrgencyLevel(): array
    {
        return match(true) {
            $this->daysRemaining <= 1 => [
                'title' => 'URGENT - Deadline dans 24h',
                'color' => 'danger'
            ],
            $this->daysRemaining <= 3 => [
                'title' => 'Deadline approche',
                'color' => 'warning'  
            ],
            $this->daysRemaining <= 7 => [
                'title' => 'Rappel deadline',
                'color' => 'info'
            ],
            default => [
                'title' => 'Deadline prochaine',
                'color' => 'gray'
            ]
        };
    }
}
