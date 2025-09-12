<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class JobFailedNotification extends Notification implements ShouldQueue
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
        $error = $this->data['data']['error'] ?? 'Erreur inconnue';
        
        return (new MailMessage)
            ->subject('⚠️ DCP Job échoué - ' . $movie->title)
            ->greeting('Attention !')
            ->line("Le traitement DCP pour le film '{$movie->title}' a échoué.")
            ->line("Type d'opération : " . $this->getJobDisplayName($jobType))
            ->line("Erreur : " . $error)
            ->action('Voir les détails', url('/admin/movies/' . $movie->id))
            ->line('Veuillez vérifier et relancer le traitement si nécessaire.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Job DCP échoué',
            'message' => "Le traitement du film '{$this->data['movie']->title}' a échoué",
            'movie_id' => $this->data['movie']->id,
            'job_type' => $this->data['job_type'],
            'error' => $this->data['data']['error'] ?? 'Erreur inconnue',
            'data' => $this->data['data'] ?? [],
            'icon' => 'heroicon-o-exclamation-circle',
            'actions' => [
                [
                    'label' => 'Voir les détails',
                    'color' => 'primary',
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
            ->title('Job DCP échoué')
            ->body("Le traitement du film '{$this->data['movie']->title}' a échoué")
            ->icon('heroicon-o-exclamation-circle')
            ->color('danger')
            ->actions([
                Action::make('view')
                    ->label('Voir les détails')
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
