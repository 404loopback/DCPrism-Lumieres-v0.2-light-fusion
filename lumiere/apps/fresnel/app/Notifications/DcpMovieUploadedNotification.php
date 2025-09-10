<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use App\Models\Movie;

class DcpMovieUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Movie $movie,
        protected array $data = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Format pour la database notification (système natif Filament)
     */
    public function toArray(object $notifiable): array
    {
        return [
            // Données Filament standards
            'title' => '🎬 Film uploadé',
            'body' => "Votre film \"{$this->movie->title}\" a été uploadé avec succès et est en cours d'analyse.",
            'icon' => 'heroicon-o-film',
            'color' => 'success',
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Voir le film',
                    'url' => "/manager/movies/{$this->movie->id}",
                    'color' => 'primary',
                ],
            ],
            
            // Métadonnées DCPrism
            'dcprism_type' => 'movie_uploaded',
            'movie_id' => $this->movie->id,
            'festival_id' => $this->movie->festival_id ?? null,
            'created_by' => $this->data['created_by'] ?? null,
            'is_important' => false,
        ];
    }

    /**
     * Format pour l'email
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("DCPrism - Film uploadé : {$this->movie->title}")
            ->greeting('Bonjour !')
            ->line("Votre film \"{$this->movie->title}\" a été uploadé avec succès.")
            ->line('Il est maintenant en cours d\'analyse par notre système.')
            ->action('Voir le film', url("/manager/movies/{$this->movie->id}"))
            ->line('Vous recevrez une notification dès que l\'analyse sera terminée.')
            ->salutation('L\'équipe DCPrism');
    }

    /**
     * Envoi de notification Filament dans l'interface (toast + database)
     */
    public static function sendToUser($user, Movie $movie, array $data = []): void
    {
        // Notification toast immédiate
        FilamentNotification::make()
            ->title('🎬 Film uploadé')
            ->body("Le film \"{$movie->title}\" a été uploadé avec succès")
            ->icon('heroicon-o-film')
            ->color('success')
            ->actions([
                Action::make('view')
                    ->label('Voir le film')
                    ->url("/manager/movies/{$movie->id}")
                    ->button()
            ])
            ->sendToDatabase($user);

        // Notification persistante (si l'utilisateur n'est pas connecté)
        $user->notify(new self($movie, $data));
    }
}
