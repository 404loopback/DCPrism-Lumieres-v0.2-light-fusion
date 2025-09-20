<?php

namespace Modules\Fresnel\app\Filament\Manager\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;
use Modules\Fresnel\app\Models\Movie;

class MoviesCardsWidget extends Widget
{
    protected string $view = 'filament.manager.widgets.movies-cards';

    protected int|string|array $columnSpan = 'full';

    public function getMovies()
    {
        $festivalId = Session::get('selected_festival_id');

        if (! $festivalId) {
            return collect();
        }

        return Movie::with(['versions', 'dcps', 'festivals'])
            ->whereHas('festivals', function ($query) use ($festivalId) {
                $query->where('festival_id', $festivalId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    public function notifySource(int $movieId): void
    {
        $movie = Movie::findOrFail($movieId);

        // Envoi d'email via MailingService
        $mailingService = app(\App\Services\MailingService::class);
        $message = "Mise à jour concernant votre film '{$movie->title}' dans DCPrism.";
        $success = $mailingService->sendSourceNotification($movie, $message);

        if ($success) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "La source {$movie->source_email} a été notifiée pour le film '{$movie->title}'.",
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Erreur lors de l'envoi de l'email.",
            ]);
        }
    }

    public function deleteMovie(int $movieId): void
    {
        $movie = Movie::findOrFail($movieId);
        $movieTitle = $movie->title;

        $movie->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Le film '{$movieTitle}' a été supprimé avec succès.",
        ]);

        $this->dispatch('$refresh');
    }
}
