<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Notification de test',
            'message' => $this->data['message'] ?? 'Ceci est une notification de test',
            'icon' => 'heroicon-o-beaker',
            'timestamp' => $this->data['timestamp'] ?? now(),
            'actions' => [
                [
                    'label' => 'Fermer',
                    'color' => 'gray',
                    'url' => '#'
                ]
            ]
        ];
    }

    /**
     * Envoi de la notification Filament (toast)
     */
    public function toFilament(object $notifiable): FilamentNotification
    {
        return FilamentNotification::make()
            ->title($this->data['title'] ?? 'Notification de test')
            ->body($this->data['message'] ?? 'Ceci est une notification de test')
            ->icon('heroicon-o-beaker')
            ->color('info')
            ->actions([
                Action::make('close')
                    ->label('Fermer')
                    ->close()
            ]);
    }
}
