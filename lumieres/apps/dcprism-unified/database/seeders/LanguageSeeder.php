<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Modules\Fresnel\app\Models\Lang;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Utilise les packages umpirsky/language-list pour obtenir toutes les langues ISO
     * avec leurs noms en anglais, français et dans leur langue native
     */
    public function run(): void
    {
        $this->command->info('🌍 Génération complète des langues ISO avec traductions françaises...');

        // Charger les données des langues depuis les packages installés
        $englishLanguages = require base_path('vendor/umpirsky/language-list/data/en/language.php');
        $frenchLanguages = require base_path('vendor/umpirsky/language-list/data/fr/language.php');
        $nativeLanguages = $this->loadNativeLanguages();

        // Mapping manuel des codes ISO 639-3 pour les langues les plus courantes
        $iso639_3_mapping = $this->getIso639_3Mapping();

        $created = 0;
        $updated = 0;

        foreach ($englishLanguages as $iso639_1 => $englishName) {
            // Ignorer les codes invalides ou trop longs
            if (strlen($iso639_1) !== 2) {
                continue;
            }

            $languageData = [
                'iso_639_1' => strtoupper($iso639_1),
                'iso_639_3' => isset($iso639_3_mapping[$iso639_1]) ? strtoupper($iso639_3_mapping[$iso639_1]) : null,
                'name' => $englishName,
                'french_name' => $frenchLanguages[$iso639_1] ?? null,
                'local_name' => $nativeLanguages[$iso639_1] ?? null,
            ];

            $language = Lang::firstOrCreate(
                ['iso_639_1' => strtoupper($iso639_1)],
                $languageData
            );

            if ($language->wasRecentlyCreated) {
                $created++;
            } else {
                // Mettre à jour avec les nouvelles données si nécessaire
                $language->update(array_filter($languageData, fn ($value) => ! is_null($value)));
                $updated++;
            }
        }

        $total = Lang::count();

        $this->command->info('✅ Génération des langues terminée !');
        $this->command->info('📊 Résultat: '.$created.' langues créées, '.$updated.' mises à jour');
        $this->command->info('🌐 Total: '.$total.' langues disponibles dans la base de données');
        $this->command->info('🇫🇷 Traductions françaises: '.Lang::whereNotNull('french_name')->count());
        $this->command->info('🏠 Noms natifs: '.Lang::whereNotNull('local_name')->count());
    }

    /**
     * Charge les noms natifs des langues depuis le package umpirsky
     */
    private function loadNativeLanguages(): array
    {
        $nativeLanguages = [];

        // Charger automatiquement les noms natifs depuis les fichiers du package
        $dataDir = base_path('vendor/umpirsky/language-list/data/');

        if (File::exists($dataDir)) {
            // Récupérer tous les dossiers de langues (codes à 2 lettres)
            $languageDirs = File::directories($dataDir);

            foreach ($languageDirs as $langDir) {
                $langCode = basename($langDir);

                // Ignorer les dossiers avec des codes longs (variantes régionales)
                if (strlen($langCode) !== 2) {
                    continue;
                }

                $languageFile = $langDir.'/language.php';

                if (File::exists($languageFile)) {
                    try {
                        $langData = require $languageFile;
                        // Prendre le nom de cette langue dans sa propre langue
                        if (isset($langData[$langCode])) {
                            $nativeLanguages[$langCode] = $langData[$langCode];
                        }
                    } catch (Exception $e) {
                        // Ignorer les erreurs de fichiers corrompus
                        continue;
                    }
                }
            }
        }

        return $nativeLanguages;
    }

    /**
     * Mapping des codes ISO 639-1 vers ISO 639-3 pour les langues les plus courantes
     */
    private function getIso639_3Mapping(): array
    {
        return [
            'aa' => 'aar', 'ab' => 'abk', 'ae' => 'ave', 'af' => 'afr', 'ak' => 'aka',
            'am' => 'amh', 'an' => 'arg', 'ar' => 'ara', 'as' => 'asm', 'av' => 'ava',
            'ay' => 'aym', 'az' => 'aze', 'ba' => 'bak', 'be' => 'bel', 'bg' => 'bul',
            'bh' => 'bih', 'bi' => 'bis', 'bm' => 'bam', 'bn' => 'ben', 'bo' => 'bod',
            'br' => 'bre', 'bs' => 'bos', 'ca' => 'cat', 'ce' => 'che', 'ch' => 'cha',
            'co' => 'cos', 'cr' => 'cre', 'cs' => 'ces', 'cu' => 'chu', 'cv' => 'chv',
            'cy' => 'cym', 'da' => 'dan', 'de' => 'deu', 'dv' => 'div', 'dz' => 'dzo',
            'ee' => 'ewe', 'el' => 'ell', 'en' => 'eng', 'eo' => 'epo', 'es' => 'spa',
            'et' => 'est', 'eu' => 'eus', 'fa' => 'fas', 'ff' => 'ful', 'fi' => 'fin',
            'fj' => 'fij', 'fo' => 'fao', 'fr' => 'fra', 'fy' => 'fry', 'ga' => 'gle',
            'gd' => 'gla', 'gl' => 'glg', 'gn' => 'grn', 'gu' => 'guj', 'gv' => 'glv',
            'ha' => 'hau', 'he' => 'heb', 'hi' => 'hin', 'ho' => 'hmo', 'hr' => 'hrv',
            'ht' => 'hat', 'hu' => 'hun', 'hy' => 'hye', 'hz' => 'her', 'ia' => 'ina',
            'id' => 'ind', 'ie' => 'ile', 'ig' => 'ibo', 'ii' => 'iii', 'ik' => 'ipk',
            'io' => 'ido', 'is' => 'isl', 'it' => 'ita', 'iu' => 'iku', 'ja' => 'jpn',
            'jv' => 'jav', 'ka' => 'kat', 'kg' => 'kon', 'ki' => 'kik', 'kj' => 'kua',
            'kk' => 'kaz', 'kl' => 'kal', 'km' => 'khm', 'kn' => 'kan', 'ko' => 'kor',
            'kr' => 'kau', 'ks' => 'kas', 'ku' => 'kur', 'kv' => 'kom', 'kw' => 'cor',
            'ky' => 'kir', 'la' => 'lat', 'lb' => 'ltz', 'lg' => 'lug', 'li' => 'lim',
            'ln' => 'lin', 'lo' => 'lao', 'lt' => 'lit', 'lu' => 'lub', 'lv' => 'lav',
            'mg' => 'mlg', 'mh' => 'mah', 'mi' => 'mri', 'mk' => 'mkd', 'ml' => 'mal',
            'mn' => 'mon', 'mr' => 'mar', 'ms' => 'msa', 'mt' => 'mlt', 'my' => 'mya',
            'na' => 'nau', 'nb' => 'nob', 'nd' => 'nde', 'ne' => 'nep', 'ng' => 'ndo',
            'nl' => 'nld', 'nn' => 'nno', 'no' => 'nor', 'nr' => 'nbl', 'nv' => 'nav',
            'ny' => 'nya', 'oc' => 'oci', 'oj' => 'oji', 'om' => 'orm', 'or' => 'ori',
            'os' => 'oss', 'pa' => 'pan', 'pi' => 'pli', 'pl' => 'pol', 'ps' => 'pus',
            'pt' => 'por', 'qu' => 'que', 'rm' => 'roh', 'rn' => 'run', 'ro' => 'ron',
            'ru' => 'rus', 'rw' => 'kin', 'sa' => 'san', 'sc' => 'srd', 'sd' => 'snd',
            'se' => 'sme', 'sg' => 'sag', 'si' => 'sin', 'sk' => 'slk', 'sl' => 'slv',
            'sm' => 'smo', 'sn' => 'sna', 'so' => 'som', 'sq' => 'sqi', 'sr' => 'srp',
            'ss' => 'ssw', 'st' => 'sot', 'su' => 'sun', 'sv' => 'swe', 'sw' => 'swa',
            'ta' => 'tam', 'te' => 'tel', 'tg' => 'tgk', 'th' => 'tha', 'ti' => 'tir',
            'tk' => 'tuk', 'tl' => 'tgl', 'tn' => 'tsn', 'to' => 'ton', 'tr' => 'tur',
            'ts' => 'tso', 'tt' => 'tat', 'tw' => 'twi', 'ty' => 'tah', 'ug' => 'uig',
            'uk' => 'ukr', 'ur' => 'urd', 'uz' => 'uzb', 've' => 'ven', 'vi' => 'vie',
            'vo' => 'vol', 'wa' => 'wln', 'wo' => 'wol', 'xh' => 'xho', 'yi' => 'yid',
            'yo' => 'yor', 'za' => 'zha', 'zh' => 'zho', 'zu' => 'zul',
        ];
    }
}
