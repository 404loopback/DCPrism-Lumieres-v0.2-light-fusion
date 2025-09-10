<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class JobCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $movie = $this->data['movie'];
        $jobType = $this->data['job_type'];
        
        return (new MailMessage)
            ->subject('DCP Job terminé - ' . $movie->title)
            ->greeting('Bonjour !')
            ->line("Le traitement DCP pour le film '{$movie->title}' s'est terminé avec succès.")
            ->line("Type d'opération : " . $this->getJobDisplayName($jobType))
            ->action('Voir le film', url('/admin/movies/' . $movie->id))
            ->line('Merci d\'utiliser DCPrism !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Job DCP terminé',
            'message' => "Le traitement du film '{$this->data['movie']->title}' est terminé",
            'movie_id' => $this->data['movie']->id,
            'job_type' => $this->data['job_type'],
            'data' => $this->data['data'] ?? [],
            'icon' => 'heroicon-o-check-badge',
            'actions' => [
                [
                    'label' => 'Voir le film',
                    'color' => 'success',
                    'url' => '/admin/movies/' . $this->data['movie']->id
                ]
            ]
        ];
    }

    /**
     * Notification Filament native
     */
    public function toFilament(object $notifiable): FilamentNotification
    {
        return FilamentNotification::make()
            ->title('Job DCP terminé')
            ->body("Le traitement du film '{$this->data['movie']->title}' est terminé avec succès")
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->actions([
                Action::make('view')
                    ->label('Voir le film')
                    ->url('/admin/movies/' . $this->data['movie']->id)
                    ->button()
            ]);
    }
    
    private function getJobDisplayName(string $jobType): string
    {
        return match($jobType) {
            'analysis' => 'Analyse DCP',
            'validation' => 'Validation DCP',
            'metadata' => 'Extraction métadonnées',
            'nomenclature' => 'Génération nomenclature',
            default => 'Traitement DCP',
        };
    }
}
