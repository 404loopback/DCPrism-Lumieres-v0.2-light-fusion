<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use App\Models\Movie;
use App\Models\Dcp;

class DcpReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Movie $movie,
        protected Dcp $dcp,
        protected array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => '▶️ DCP prêt !',
            'body' => "Le DCP pour \"{$this->movie->title}\" est maintenant disponible au téléchargement.",
            'icon' => 'heroicon-o-play',
            'color' => 'success',
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'download',
                    'label' => 'Télécharger',
                    'url' => "/manager/dcps/{$this->dcp->id}/download",
                    'color' => 'success',
                ],
                [
                    'name' => 'view',
                    'label' => 'Voir détails',
                    'url' => "/manager/movies/{$this->movie->id}",
                    'color' => 'primary',
                ],
            ],
            
            'dcprism_type' => 'dcp_ready',
            'movie_id' => $this->movie->id,
            'dcp_id' => $this->dcp->id,
            'festival_id' => $this->movie->festival_id ?? null,
            'is_important' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("DCPrism - DCP prêt : {$this->movie->title}")
            ->greeting('Excellente nouvelle !')
            ->line("Le DCP pour votre film \"{$this->movie->title}\" est maintenant disponible !")
            ->line('Vous pouvez le télécharger dès maintenant depuis votre tableau de bord.')
            ->action('Télécharger le DCP', url("/manager/dcps/{$this->dcp->id}/download"))
            ->line('Le fichier est prêt pour la distribution et la projection.')
            ->salutation('L\'équipe DCPrism');
    }

    public static function sendToUser($user, Movie $movie, Dcp $dcp, array $data = []): void
    {
        FilamentNotification::make()
            ->title('▶️ DCP prêt !')
            ->body("Le DCP pour \"{$movie->title}\" est disponible")
            ->icon('heroicon-o-play')
            ->color('success')
            ->actions([
                Action::make('download')
                    ->label('Télécharger')
                    ->url("/manager/dcps/{$dcp->id}/download")
                    ->button()
                    ->color('success'),
                Action::make('view')
                    ->label('Voir détails')
                    ->url("/manager/movies/{$movie->id}")
                    ->button()
                    ->outlined()
            ])
            ->sendToDatabase($user);

        $user->notify(new self($movie, $dcp, $data));
    }
}
