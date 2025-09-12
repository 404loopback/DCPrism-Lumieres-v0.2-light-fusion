<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Festival;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ShowcaseController extends Controller
{
    /**
     * Page d'accueil du site vitrine
     */
    public function home(): View
    {
        $stats = $this->getStats();
        
        return view('showcase.home', compact('stats'));
    }

    /**
     * Page des fonctionnalités
     */
    public function features(): View
    {
        return view('showcase.features');
    }

    /**
     * Page à propos
     */
    public function about(): View
    {
        return view('showcase.about');
    }

    /**
     * Page de contact
     */
    public function contact(): View
    {
        return view('showcase.contact');
    }

    /**
     * Traiter le formulaire de contact
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Ici vous pouvez ajouter la logique d'envoi d'email
            // Mail::to(config('mail.admin_email'))->send(new ContactMessage($validated));
            
            Log::info('Contact form submission', $validated);
            
            return redirect()->route('showcase.contact')
                ->with('success', 'Votre message a été envoyé avec succès! Nous vous recontacterons bientôt.');
        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());
            
            return redirect()->route('showcase.contact')
                ->with('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.');
        }
    }

    /**
     * API pour les statistiques en temps réel
     */
    public function apiStats()
    {
        return response()->json($this->getStats());
    }

    /**
     * Obtenir les statistiques de la plateforme
     */
    private function getStats(): array
    {
        return [
            'total_movies' => Movie::count(),
            'total_festivals' => Festival::active()->count(),
            'validated_movies' => Movie::technicallyValidated()->count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'processing_movies' => Movie::pendingTechnicalValidation()->count(),
            'storage_used' => $this->getStorageStats(),
        ];
    }

    /**
     * Statistiques de stockage
     */
    private function getStorageStats(): string
    {
        $totalSize = Movie::sum('file_size') ?? 0;
        
        if ($totalSize >= 1024 * 1024 * 1024 * 1024) {
            return number_format($totalSize / (1024 * 1024 * 1024 * 1024), 1) . ' TB';
        } elseif ($totalSize >= 1024 * 1024 * 1024) {
            return number_format($totalSize / (1024 * 1024 * 1024), 1) . ' GB';
        } elseif ($totalSize >= 1024 * 1024) {
            return number_format($totalSize / (1024 * 1024), 1) . ' MB';
        } else {
            return number_format($totalSize / 1024, 1) . ' KB';
        }
    }
}
