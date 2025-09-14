<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\{Movie, Festival, Version, User};
use Illuminate\Support\Facades\Log;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = [
            // Films pour Cannes (longs métrages prestigieux)
            [
                'title' => 'Le Dernier Voyage',
                'source_email' => 'prod@cinemaeuropeen.fr',
                'status' => 'validated',
                'description' => 'Un drame poignant sur les derniers jours d\'un voyageur solitaire',
                'duration' => 142,
                'genre' => 'Drame',
                'year' => 2024,
                'country' => 'France',
                'language' => 'Français',
                'validated_at' => now()->subDays(2),
                'file_size' => 45 * 1024 * 1024 * 1024, // 45GB
                'uploaded_at' => now()->subDays(5),
                'DCP_metadata' => [
                    'resolution' => '4K',
                    'framerate' => '24fps',
                    'audio_channels' => '7.1',
                    'color_space' => 'Rec. 2020',
                    'aspect_ratio' => '2.39:1',
                    'duration_frames' => 205920,
                    'encryption' => false
                ],
                'festivals' => ['cannes'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'FTR', 'audio_lang' => 'fra'],
                    ['type' => 'VOST', 'format' => 'FTR', 'audio_lang' => 'fra', 'sub_lang' => 'eng'],
                    ['type' => 'VOSTF', 'format' => 'FTR', 'audio_lang' => 'fra', 'sub_lang' => 'fra']
                ]
            ],
            [
                'title' => 'Shadows in the Night',
                'source_email' => 'submissions@indiefilms.com',
                'status' => 'in_review',
                'description' => 'A noir thriller set in 1940s New York',
                'duration' => 118,
                'genre' => 'Thriller',
                'year' => 2024,
                'country' => 'USA',
                'language' => 'English',
                'file_size' => 38 * 1024 * 1024 * 1024, // 38GB
                'uploaded_at' => now()->subDays(3),
                'DCP_metadata' => [
                    'resolution' => '2K',
                    'framerate' => '24fps',
                    'audio_channels' => '5.1',
                    'color_space' => 'Rec. 709',
                    'aspect_ratio' => '1.85:1',
                    'duration_frames' => 169920,
                    'encryption' => true
                ],
                'festivals' => ['cannes', 'berlinale'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'FTR', 'audio_lang' => 'eng'],
                    ['type' => 'VOST', 'format' => 'FTR', 'audio_lang' => 'eng', 'sub_lang' => 'fra'],
                    ['type' => 'VF', 'format' => 'FTR', 'audio_lang' => 'fra']
                ]
            ],
            [
                'title' => 'The Artist\'s Dilemma',
                'source_email' => 'indie@artfilm.fr',
                'status' => 'distributed',
                'description' => 'Un portrait intime d\'un peintre face à ses démons créatifs',
                'duration' => 97,
                'genre' => 'Drame',
                'year' => 2024,
                'country' => 'France',
                'language' => 'Français',
                'validated_at' => now()->subDays(10),
                'file_size' => 32 * 1024 * 1024 * 1024, // 32GB
                'uploaded_at' => now()->subDays(15),
                'DCP_metadata' => [
                    'resolution' => '2K',
                    'framerate' => '25fps',
                    'audio_channels' => '5.1',
                    'color_space' => 'Rec. 709',
                    'aspect_ratio' => '1.66:1',
                    'duration_frames' => 145500,
                    'encryption' => false
                ],
                'festivals' => ['cannes'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'FTR', 'audio_lang' => 'fra'],
                    ['type' => 'VOST', 'format' => 'FTR', 'audio_lang' => 'fra', 'sub_lang' => 'eng']
                ]
            ],
            
            // Courts métrages pour Clermont-Ferrand
            [
                'title' => 'Fragments',
                'source_email' => 'contact@shortfilmstudio.be',
                'status' => 'upload_ok',
                'description' => 'Court métrage expérimental sur la mémoire',
                'duration' => 23,
                'genre' => 'Expérimental',
                'year' => 2024,
                'country' => 'Belgium',
                'language' => 'Sans dialogue',
                'file_size' => 8 * 1024 * 1024 * 1024, // 8GB
                'uploaded_at' => now()->subHours(6),
                'DCP_metadata' => [
                    'resolution' => '2K',
                    'framerate' => '24fps',
                    'audio_channels' => '2.0',
                    'color_space' => 'Rec. 709',
                    'aspect_ratio' => '1.85:1',
                    'duration_frames' => 33120,
                    'encryption' => false
                ],
                'festivals' => ['clermont-ferrand'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'SHR', 'audio_lang' => 'zxx'] // zxx = no linguistic content
                ]
            ],
            [
                'title' => 'La Dernière Danse',
                'source_email' => 'court@cinema-paris.fr',
                'status' => 'validated',
                'description' => 'L\'histoire touchante d\'une ballerine à la retraite',
                'duration' => 18,
                'genre' => 'Drame',
                'year' => 2024,
                'country' => 'France',
                'language' => 'Français',
                'validated_at' => now()->subDays(4),
                'file_size' => 6 * 1024 * 1024 * 1024, // 6GB
                'uploaded_at' => now()->subDays(8),
                'DCP_metadata' => [
                    'resolution' => '2K',
                    'framerate' => '25fps',
                    'audio_channels' => '5.1',
                    'color_space' => 'Rec. 709',
                    'aspect_ratio' => '1.66:1',
                    'duration_frames' => 27000,
                    'encryption' => false
                ],
                'festivals' => ['clermont-ferrand'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'SHR', 'audio_lang' => 'fra'],
                    ['type' => 'VOST', 'format' => 'SHR', 'audio_lang' => 'fra', 'sub_lang' => 'eng']
                ]
            ],
            [
                'title' => 'Urban Pulse',
                'source_email' => 'short@urbanfilms.de',
                'status' => 'in_review',
                'description' => 'Un regard moderne sur la vie urbaine contemporaine',
                'duration' => 12,
                'genre' => 'Documentaire',
                'year' => 2024,
                'country' => 'Germany',
                'language' => 'Deutsch',
                'file_size' => 4 * 1024 * 1024 * 1024, // 4GB
                'uploaded_at' => now()->subDays(2),
                'DCP_metadata' => [
                    'resolution' => '2K',
                    'framerate' => '24fps',
                    'audio_channels' => '2.0',
                    'color_space' => 'Rec. 709',
                    'aspect_ratio' => '1.78:1',
                    'duration_frames' => 17280,
                    'encryption' => false
                ],
                'festivals' => ['clermont-ferrand', 'berlinale'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'SHR', 'audio_lang' => 'deu'],
                    ['type' => 'VOST', 'format' => 'SHR', 'audio_lang' => 'deu', 'sub_lang' => 'eng'],
                    ['type' => 'VOSTF', 'format' => 'SHR', 'audio_lang' => 'deu', 'sub_lang' => 'fra']
                ]
            ],
            
            // Bandes-annonces et contenus spéciaux
            [
                'title' => 'Bande-annonce - Epic Adventure',
                'source_email' => 'marketing@blockbuster.com',
                'status' => 'pending',
                'description' => 'Bande-annonce du prochain blockbuster été',
                'duration' => 3,
                'genre' => 'Action',
                'year' => 2024,
                'country' => 'USA',
                'language' => 'English',
                'file_size' => 2 * 1024 * 1024 * 1024, // 2GB
                'DCP_metadata' => [
                    'resolution' => '4K',
                    'framerate' => '24fps',
                    'audio_channels' => '7.1',
                    'color_space' => 'Rec. 2020',
                    'aspect_ratio' => '2.39:1',
                    'duration_frames' => 4320,
                    'encryption' => true
                ],
                'festivals' => ['cannes'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'TRL', 'audio_lang' => 'eng'],
                    ['type' => 'VF', 'format' => 'TRL', 'audio_lang' => 'fra']
                ]
            ],
            [
                'title' => 'Documentary: Life on Mars',
                'source_email' => 'science@documentaires.org',
                'status' => 'rejected',
                'description' => 'Documentaire sur l\'exploration de Mars',
                'duration' => 95,
                'genre' => 'Documentaire',
                'year' => 2024,
                'country' => 'Canada',
                'language' => 'English',
                'technical_notes' => 'Problème de synchro audio détecté, rejeté pour correction',
                'validated_at' => now()->subDays(1),
                'file_size' => 28 * 1024 * 1024 * 1024, // 28GB
                'uploaded_at' => now()->subDays(12),
                'DCP_metadata' => [
                    'resolution' => '2K',
                    'framerate' => '24fps',
                    'audio_channels' => '5.1',
                    'color_space' => 'Rec. 709',
                    'aspect_ratio' => '1.78:1',
                    'duration_frames' => 136800,
                    'encryption' => false
                ],
                'festivals' => ['berlinale'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'FTR', 'audio_lang' => 'eng'],
                    ['type' => 'VF', 'format' => 'FTR', 'audio_lang' => 'fra'],
                    ['type' => 'VOSTF', 'format' => 'FTR', 'audio_lang' => 'eng', 'sub_lang' => 'fra']
                ]
            ],
            [
                'title' => 'Midnight Express',
                'source_email' => 'indie@nightowlfilms.com',
                'status' => 'upload_error',
                'description' => 'Thriller psychologique dans le métro parisien',
                'duration' => 89,
                'genre' => 'Thriller',
                'year' => 2024,
                'country' => 'France',
                'language' => 'Français',
                'technical_notes' => 'Échec de l\'upload : fichier corrompu détecté',
                'file_size' => 0, // Upload échoué
                'festivals' => ['cannes'],
                'versions' => [
                    ['type' => 'VO', 'format' => 'FTR', 'audio_lang' => 'fra'],
                    ['type' => 'VOST', 'format' => 'FTR', 'audio_lang' => 'fra', 'sub_lang' => 'eng']
                ]
            ]
        ];

        // Récupérer les festivals existants
        $festivals = Festival::pluck('id', 'subdomain')->toArray();
        
        foreach ($movies as $movieData) {
            // Extraire les données spéciales avant création
            $versions = $movieData['versions'] ?? [];
            $festivalSubdomains = $movieData['festivals'] ?? [];
            unset($movieData['versions'], $movieData['festivals']);
            
            // Créer ou récupérer le film
            $movie = Movie::firstOrCreate(
                ['title' => $movieData['title']],
                $movieData
            );
            
            // Associer le film aux festivals
            foreach ($festivalSubdomains as $subdomain) {
                if (isset($festivals[$subdomain])) {
                    $movie->festivals()->syncWithoutDetaching([
                        $festivals[$subdomain] => [
                            'submission_status' => $this->getSubmissionStatus($movie->status),
                            'selected_versions' => json_encode(array_column($versions, 'type')),
                            'technical_notes' => $movie->technical_notes,
                            'priority' => rand(1, 5)
                        ]
                    ]);
                }
            }
            
            // Créer les versions associées
            foreach ($versions as $versionData) {
                $versionData['movie_id'] = $movie->id;
                Version::firstOrCreate(
                    [
                        'movie_id' => $movie->id, 
                        'type' => $versionData['type'],
                        'format' => $versionData['format']
                    ],
                    $versionData
                );
            }
        }
        
        Log::info('MovieSeeder completed', [
            'total_movies' => Movie::count(),
            'total_versions' => Version::count(),
            'movies_with_festivals' => Movie::has('festivals')->count()
        ]);
    }
    
    /**
     * Détermine le statut de soumission basé sur le statut du film
     */
    private function getSubmissionStatus(string $movieStatus): string
    {
        return match($movieStatus) {
            'validated', 'distributed' => 'accepted',
            'rejected' => 'rejected',
            'in_review' => 'under_review',
            'upload_ok' => 'submitted',
            'upload_error' => 'failed',
            default => 'pending'
        };
    }
}
