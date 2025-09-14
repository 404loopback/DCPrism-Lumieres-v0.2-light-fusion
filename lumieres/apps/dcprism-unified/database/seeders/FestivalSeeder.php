<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Festival;

class FestivalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $festivals = [
            [
                'name' => 'Festival International du Film de Cannes',
                'subdomain' => 'cannes',
                'description' => 'Le plus prestigieux festival de cinéma au monde',
                'email' => 'tech@festival-cannes.com',
                'website' => 'https://www.festival-cannes.com',
                'contact_phone' => '+33 4 92 99 84 00',
                'start_date' => '2024-05-14',
                'end_date' => '2024-05-25',
                'submission_deadline' => '2024-03-01 23:59:59',
                'is_active' => true,
                'accept_submissions' => true,
                'accepted_formats' => ['FTR', 'SHR', 'TRL'],
                'max_storage' => 500 * 1024 * 1024 * 1024, // 500GB
                'max_file_size' => 50 * 1024 * 1024 * 1024, // 50GB
                'backblaze_folder' => 'cannes-2024',
                'technical_requirements' => [
                    'resolution' => '2K minimum, 4K accepté',
                    'format' => 'DCP SMPTE',
                    'audio' => '5.1 ou 7.1 surround',
                    'subtitles' => 'Français et Anglais obligatoires'
                ]
            ],
            [
                'name' => 'Festival du Film de Berlin (Berlinale)',
                'subdomain' => 'berlinale',
                'description' => 'Un des festivals les plus importants d\'Europe',
                'email' => 'dcp@berlinale.de',
                'website' => 'https://www.berlinale.de',
                'contact_phone' => '+49 30 259 200',
                'start_date' => '2024-02-15',
                'end_date' => '2024-02-25',
                'submission_deadline' => '2024-01-15 23:59:59',
                'is_active' => false, // Festival passé
                'accept_submissions' => false,
                'accepted_formats' => ['FTR', 'SHR', 'EPS'],
                'max_storage' => 300 * 1024 * 1024 * 1024, // 300GB
                'max_file_size' => 30 * 1024 * 1024 * 1024, // 30GB
                'backblaze_folder' => 'berlinale-2024',
            ],
            [
                'name' => 'Festival du Court Métrage de Clermont-Ferrand',
                'subdomain' => 'clermont-ferrand',
                'description' => 'Le plus grand festival mondial de court métrage',
                'email' => 'technique@clermont-filmfest.org',
                'website' => 'https://www.clermont-filmfest.org',
                'contact_phone' => '+33 4 73 91 65 73',
                'start_date' => '2024-01-26',
                'end_date' => '2024-02-03',
                'submission_deadline' => '2023-12-01 23:59:59',
                'is_active' => true,
                'accept_submissions' => false, // Deadline passée
                'accepted_formats' => ['SHR'], // Uniquement courts métrages
                'max_storage' => 100 * 1024 * 1024 * 1024, // 100GB
                'max_file_size' => 10 * 1024 * 1024 * 1024, // 10GB
                'backblaze_folder' => 'clermont-2024',
                'technical_requirements' => [
                    'duration' => 'Maximum 30 minutes',
                    'format' => 'DCP ou ProRes',
                    'subtitles' => 'Anglais recommandés'
                ]
            ]
        ];

        foreach ($festivals as $festivalData) {
            Festival::firstOrCreate(
                ['subdomain' => $festivalData['subdomain']],
                $festivalData
            );
        }
    }
}
