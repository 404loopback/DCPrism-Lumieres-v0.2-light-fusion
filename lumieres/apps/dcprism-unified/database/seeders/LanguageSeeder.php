<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Lang;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            // Langues principales européennes
            ['iso_639_1' => 'fr', 'iso_639_3' => 'fra', 'name' => 'French', 'local_name' => 'Français'],
            ['iso_639_1' => 'en', 'iso_639_3' => 'eng', 'name' => 'English', 'local_name' => 'English'],
            ['iso_639_1' => 'es', 'iso_639_3' => 'spa', 'name' => 'Spanish', 'local_name' => 'Español'],
            ['iso_639_1' => 'it', 'iso_639_3' => 'ita', 'name' => 'Italian', 'local_name' => 'Italiano'],
            ['iso_639_1' => 'de', 'iso_639_3' => 'deu', 'name' => 'German', 'local_name' => 'Deutsch'],
            ['iso_639_1' => 'pt', 'iso_639_3' => 'por', 'name' => 'Portuguese', 'local_name' => 'Português'],
            ['iso_639_1' => 'nl', 'iso_639_3' => 'nld', 'name' => 'Dutch', 'local_name' => 'Nederlands'],
            ['iso_639_1' => 'sv', 'iso_639_3' => 'swe', 'name' => 'Swedish', 'local_name' => 'Svenska'],
            ['iso_639_1' => 'da', 'iso_639_3' => 'dan', 'name' => 'Danish', 'local_name' => 'Dansk'],
            ['iso_639_1' => 'no', 'iso_639_3' => 'nor', 'name' => 'Norwegian', 'local_name' => 'Norsk'],
            
            // Langues d'Europe de l'Est
            ['iso_639_1' => 'ru', 'iso_639_3' => 'rus', 'name' => 'Russian', 'local_name' => 'Русский'],
            ['iso_639_1' => 'pl', 'iso_639_3' => 'pol', 'name' => 'Polish', 'local_name' => 'Polski'],
            ['iso_639_1' => 'cs', 'iso_639_3' => 'ces', 'name' => 'Czech', 'local_name' => 'Čeština'],
            ['iso_639_1' => 'sk', 'iso_639_3' => 'slk', 'name' => 'Slovak', 'local_name' => 'Slovenčina'],
            ['iso_639_1' => 'hu', 'iso_639_3' => 'hun', 'name' => 'Hungarian', 'local_name' => 'Magyar'],
            ['iso_639_1' => 'ro', 'iso_639_3' => 'ron', 'name' => 'Romanian', 'local_name' => 'Română'],
            
            // Langues asiatiques importantes
            ['iso_639_1' => 'ja', 'iso_639_3' => 'jpn', 'name' => 'Japanese', 'local_name' => '日本語'],
            ['iso_639_1' => 'ko', 'iso_639_3' => 'kor', 'name' => 'Korean', 'local_name' => '한국어'],
            ['iso_639_1' => 'zh', 'iso_639_3' => 'zho', 'name' => 'Chinese', 'local_name' => '中文'],
            ['iso_639_1' => 'hi', 'iso_639_3' => 'hin', 'name' => 'Hindi', 'local_name' => 'हिन्दी'],
            ['iso_639_1' => 'th', 'iso_639_3' => 'tha', 'name' => 'Thai', 'local_name' => 'ไทย'],
            ['iso_639_1' => 'vi', 'iso_639_3' => 'vie', 'name' => 'Vietnamese', 'local_name' => 'Tiếng Việt'],
            
            // Langues du Moyen-Orient et Afrique
            ['iso_639_1' => 'ar', 'iso_639_3' => 'ara', 'name' => 'Arabic', 'local_name' => 'العربية'],
            ['iso_639_1' => 'he', 'iso_639_3' => 'heb', 'name' => 'Hebrew', 'local_name' => 'עברית'],
            ['iso_639_1' => 'tr', 'iso_639_3' => 'tur', 'name' => 'Turkish', 'local_name' => 'Türkçe'],
            ['iso_639_1' => 'fa', 'iso_639_3' => 'fas', 'name' => 'Persian', 'local_name' => 'فارسی'],
            
            // Langues des Amériques
            ['iso_639_1' => 'ca', 'iso_639_3' => 'cat', 'name' => 'Catalan', 'local_name' => 'Català'],
            ['iso_639_1' => 'eu', 'iso_639_3' => 'eus', 'name' => 'Basque', 'local_name' => 'Euskera'],
            
            // Langues nordiques et baltes
            ['iso_639_1' => 'fi', 'iso_639_3' => 'fin', 'name' => 'Finnish', 'local_name' => 'Suomi'],
            ['iso_639_1' => 'is', 'iso_639_3' => 'isl', 'name' => 'Icelandic', 'local_name' => 'Íslenska'],
            ['iso_639_1' => 'et', 'iso_639_3' => 'est', 'name' => 'Estonian', 'local_name' => 'Eesti'],
            ['iso_639_1' => 'lv', 'iso_639_3' => 'lav', 'name' => 'Latvian', 'local_name' => 'Latviešu'],
            ['iso_639_1' => 'lt', 'iso_639_3' => 'lit', 'name' => 'Lithuanian', 'local_name' => 'Lietuvių'],
            
            // Langues balkaniques
            ['iso_639_1' => 'hr', 'iso_639_3' => 'hrv', 'name' => 'Croatian', 'local_name' => 'Hrvatski'],
            ['iso_639_1' => 'sr', 'iso_639_3' => 'srp', 'name' => 'Serbian', 'local_name' => 'Српски'],
            ['iso_639_1' => 'bs', 'iso_639_3' => 'bos', 'name' => 'Bosnian', 'local_name' => 'Bosanski'],
            ['iso_639_1' => 'sl', 'iso_639_3' => 'slv', 'name' => 'Slovenian', 'local_name' => 'Slovenščina'],
            ['iso_639_1' => 'mk', 'iso_639_3' => 'mkd', 'name' => 'Macedonian', 'local_name' => 'Македонски'],
            ['iso_639_1' => 'bg', 'iso_639_3' => 'bul', 'name' => 'Bulgarian', 'local_name' => 'Български'],
            ['iso_639_1' => 'el', 'iso_639_3' => 'ell', 'name' => 'Greek', 'local_name' => 'Ελληνικά'],
            
            // Autres langues européennes
            ['iso_639_1' => 'ga', 'iso_639_3' => 'gle', 'name' => 'Irish', 'local_name' => 'Gaeilge'],
            ['iso_639_1' => 'cy', 'iso_639_3' => 'cym', 'name' => 'Welsh', 'local_name' => 'Cymraeg'],
            ['iso_639_1' => 'mt', 'iso_639_3' => 'mlt', 'name' => 'Maltese', 'local_name' => 'Malti'],
        ];

        foreach ($languages as $languageData) {
            Lang::firstOrCreate(
                ['iso_639_1' => $languageData['iso_639_1']],
                $languageData
            );
        }
        
        $this->command->info('Langues créées avec succès !');
        $this->command->info('Total: ' . count($languages) . ' langues disponibles');
    }
}
