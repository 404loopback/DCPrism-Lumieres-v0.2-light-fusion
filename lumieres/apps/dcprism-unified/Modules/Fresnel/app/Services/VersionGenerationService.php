<?php

namespace Modules\Fresnel\app\Services;

use Modules\Fresnel\app\Models\Movie;
use Modules\Fresnel\app\Models\Festival;
use Modules\Fresnel\app\Models\Version;
use Modules\Fresnel\app\Models\Nomenclature;
use Modules\Fresnel\app\Models\Parameter;
use Modules\Fresnel\app\Models\MovieParameter;
use Modules\Fresnel\app\Models\FestivalParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VersionGenerationService
{
    /**
     * Générer automatiquement les versions pour un film basé sur les nomenclatures du festival
     */
    public function generateVersionsForMovie(Movie $movie, int $festivalId): array
    {
        Log::info("Generating versions for movie {$movie->id} and festival {$festivalId}");
        
        $generatedVersions = [];
        
        DB::transaction(function () use ($movie, $festivalId, &$generatedVersions) {
            // 1. Récupérer les nomenclatures actives du festival qui concernent les versions
            $nomenclatures = $this->getVersionNomenclatures($festivalId);
            
            if ($nomenclatures->isEmpty()) {
                Log::info("No version nomenclatures found for festival {$festivalId}");
                // Créer au moins une version par défaut VO
                $generatedVersions[] = $this->createDefaultVersion($movie);
                return;
            }
            
            // 2. Pour chaque nomenclature, déterminer les versions à créer
            foreach ($nomenclatures as $nomenclature) {
                $versions = $this->generateVersionsFromNomenclature($movie, $nomenclature, $festivalId);
                $generatedVersions = array_merge($generatedVersions, $versions);
            }
            
            // 3. Déduplication des versions identiques
            $generatedVersions = $this->deduplicateVersions($generatedVersions);
            
            // 4. Créer les enregistrements Version
            foreach ($generatedVersions as $versionData) {
                $version = Version::create([
                    'movie_id' => $movie->id,
                    'type' => $versionData['type'],
                    'audio_lang' => $versionData['audio_lang'],
                    'sub_lang' => $versionData['sub_lang'],
                    'accessibility' => $versionData['accessibility'] ?? null,
                    'ov_id' => null, // À définir plus tard si nécessaire
                    'vf_ids' => null,
                    'generated_nomenclature' => $versionData['nomenclature']
                ]);
                
                Log::info("Created version {$version->id}: {$version->type} - {$version->audio_lang}");
            }
        });
        
        return $generatedVersions;
    }
    
    /**
     * Récupérer les nomenclatures actives d'un festival qui concernent les versions
     */
    private function getVersionNomenclatures(int $festivalId): \Illuminate\Database\Eloquent\Collection
    {
        return Nomenclature::where('festival_id', $festivalId)
            ->where('is_active', true)
            ->whereHas('parameter', function ($query) {
                // Paramètres qui influencent les versions (audio, sous-titres, accessibilité)
                $query->where('is_active', true)
                      ->whereIn('category', ['audio', 'accessibility', 'version']);
            })
            ->with('parameter')
            ->orderBy('order_position')
            ->get();
    }
    
    /**
     * Générer les versions à partir d'une nomenclature donnée
     */
    private function generateVersionsFromNomenclature(Movie $movie, Nomenclature $nomenclature, int $festivalId): array
    {
        $parameter = $nomenclature->parameter;
        $versions = [];
        
        // Récupérer la valeur du paramètre pour ce film
        $movieParameter = $movie->movieParameters()
            ->where('parameter_id', $parameter->id)
            ->first();
        
        $parameterValue = $movieParameter ? $movieParameter->value : $parameter->default_value;
        
        if (empty($parameterValue)) {
            return [];
        }
        
        // Générer les versions selon le type de paramètre
        switch ($parameter->category) {
            case 'audio':
                $versions = $this->generateAudioVersions($movie, $parameter, $parameterValue, $nomenclature);
                break;
            case 'accessibility':
                $versions = $this->generateAccessibilityVersions($movie, $parameter, $parameterValue, $nomenclature);
                break;
            case 'version':
                $versions = $this->generateCustomVersions($movie, $parameter, $parameterValue, $nomenclature);
                break;
        }
        
        return $versions;
    }
    
    /**
     * Générer les versions audio (VO, VF, etc.)
     */
    private function generateAudioVersions(Movie $movie, Parameter $parameter, $value, Nomenclature $nomenclature): array
    {
        $versions = [];
        
        // Si le paramètre a des valeurs possibles définies
        if (!empty($parameter->possible_values)) {
            $audioLanguages = is_array($value) ? $value : explode(',', $value);
            
            foreach ($audioLanguages as $lang) {
                $lang = trim($lang);
                $versionType = $this->mapLanguageToVersionType($lang);
                
                $versions[] = [
                    'type' => $versionType,
                    'audio_lang' => $this->mapLanguageToISOCode($lang),
                    'sub_lang' => null,
                    'nomenclature' => $nomenclature->formatValue($lang, $movie)
                ];
            }
        } else {
            // Valeur libre - essayer de détecter automatiquement
            $detectedVersions = $this->detectVersionTypesFromValue($value);
            foreach ($detectedVersions as $detected) {
                $versions[] = [
                    'type' => $detected['type'],
                    'audio_lang' => $detected['audio_lang'],
                    'sub_lang' => $detected['sub_lang'],
                    'nomenclature' => $nomenclature->formatValue($value, $movie)
                ];
            }
        }
        
        return $versions;
    }
    
    /**
     * Générer les versions d'accessibilité (sous-titres, audiodescription)
     */
    private function generateAccessibilityVersions(Movie $movie, Parameter $parameter, $value, Nomenclature $nomenclature): array
    {
        $versions = [];
        
        if (str_contains(strtolower($parameter->name), 'sous-titre') || 
            str_contains(strtolower($parameter->name), 'subtitle')) {
            
            $subtitleLanguages = is_array($value) ? $value : explode(',', $value);
            
            foreach ($subtitleLanguages as $lang) {
                $lang = trim($lang);
                
                $versions[] = [
                    'type' => 'VOST',
                    'audio_lang' => 'original', // À déterminer selon le film
                    'sub_lang' => $this->mapLanguageToISOCode($lang),
                    'accessibility' => 'subtitles',
                    'nomenclature' => $nomenclature->formatValue($lang, $movie)
                ];
            }
        }
        
        return $versions;
    }
    
    /**
     * Générer des versions personnalisées
     */
    private function generateCustomVersions(Movie $movie, Parameter $parameter, $value, Nomenclature $nomenclature): array
    {
        $versions = [];
        
        // Logique personnalisée selon le nom du paramètre
        if (str_contains(strtolower($parameter->name), 'version')) {
            $versionTypes = is_array($value) ? $value : explode(',', $value);
            
            foreach ($versionTypes as $type) {
                $type = trim($type);
                $detected = $this->detectVersionTypeFromString($type);
                
                $versions[] = [
                    'type' => $detected['type'],
                    'audio_lang' => $detected['audio_lang'],
                    'sub_lang' => $detected['sub_lang'],
                    'nomenclature' => $nomenclature->formatValue($type, $movie)
                ];
            }
        }
        
        return $versions;
    }
    
    /**
     * Mapper une langue vers un type de version
     */
    private function mapLanguageToVersionType(string $language): string
    {
        $language = strtolower(trim($language));
        
        return match ($language) {
            'français', 'french', 'fr', 'fra' => 'DUB',
            'original', 'orig', 'vo' => 'VO',
            'anglais', 'english', 'en', 'eng' => 'DUB',
            'muet', 'mute', 'silent' => 'MUTE',
            default => 'VO'
        };
    }
    
    /**
     * Mapper une langue vers un code ISO
     */
    private function mapLanguageToISOCode(string $language): string
    {
        $language = strtolower(trim($language));
        
        return match ($language) {
            'français', 'french', 'fr', 'fra' => 'fr',
            'anglais', 'english', 'en', 'eng' => 'en',
            'espagnol', 'spanish', 'es', 'esp' => 'es',
            'allemand', 'german', 'de', 'deu' => 'de',
            'italien', 'italian', 'it', 'ita' => 'it',
            'original', 'orig', 'vo' => 'original',
            default => $language
        };
    }
    
    /**
     * Détecter les types de versions depuis une valeur libre
     */
    private function detectVersionTypesFromValue(string $value): array
    {
        $value = strtoupper($value);
        $versions = [];
        
        // Détecter VO
        if (str_contains($value, 'VO')) {
            $versions[] = [
                'type' => 'VO',
                'audio_lang' => 'original',
                'sub_lang' => null
            ];
        }
        
        // Détecter VOST
        if (str_contains($value, 'VOST')) {
            $subtitleLang = 'fr'; // Par défaut français
            if (str_contains($value, 'VOST-FR') || str_contains($value, 'VOSTFR')) {
                $subtitleLang = 'fr';
            } elseif (str_contains($value, 'VOST-EN')) {
                $subtitleLang = 'en';
            }
            
            $versions[] = [
                'type' => 'VOST',
                'audio_lang' => 'original',
                'sub_lang' => $subtitleLang
            ];
        }
        
        // Détecter DUB
        if (str_contains($value, 'DUB') || str_contains($value, 'VF')) {
            $versions[] = [
                'type' => 'DUB',
                'audio_lang' => 'fr',
                'sub_lang' => null
            ];
        }
        
        // Détecter DUBST
        if (str_contains($value, 'DUBST')) {
            $versions[] = [
                'type' => 'DUBST',
                'audio_lang' => 'fr',
                'sub_lang' => 'fr'
            ];
        }
        
        // Détecter MUTE
        if (str_contains($value, 'MUTE') || str_contains($value, 'MUET')) {
            $versions[] = [
                'type' => 'MUTE',
                'audio_lang' => null,
                'sub_lang' => null
            ];
        }
        
        // Si rien détecté, créer une version par défaut
        if (empty($versions)) {
            $versions[] = [
                'type' => 'VO',
                'audio_lang' => 'original',
                'sub_lang' => null
            ];
        }
        
        return $versions;
    }
    
    /**
     * Détecter le type de version depuis une chaîne
     */
    private function detectVersionTypeFromString(string $versionString): array
    {
        $upper = strtoupper(trim($versionString));
        
        if ($upper === 'VO' || str_contains($upper, 'ORIGINAL')) {
            return ['type' => 'VO', 'audio_lang' => 'original', 'sub_lang' => null];
        }
        
        if ($upper === 'DUB' || $upper === 'VF' || str_contains($upper, 'FRANÇAIS') || str_contains($upper, 'DUBB')) {
            return ['type' => 'DUB', 'audio_lang' => 'fr', 'sub_lang' => null];
        }
        
        if (str_contains($upper, 'VOST')) {
            $subLang = str_contains($upper, 'FR') ? 'fr' : 'en';
            return ['type' => 'VOST', 'audio_lang' => 'original', 'sub_lang' => $subLang];
        }
        
        if (str_contains($upper, 'DUBST')) {
            return ['type' => 'DUBST', 'audio_lang' => 'fr', 'sub_lang' => 'fr'];
        }
        
        if (str_contains($upper, 'DUB')) {
            return ['type' => 'DUB', 'audio_lang' => 'fr', 'sub_lang' => null];
        }
        
        if (str_contains($upper, 'MUTE') || str_contains($upper, 'MUET')) {
            return ['type' => 'MUTE', 'audio_lang' => null, 'sub_lang' => null];
        }
        
        // Par défaut
        return ['type' => 'VO', 'audio_lang' => 'original', 'sub_lang' => null];
    }
    
    /**
     * Créer une version par défaut si aucune nomenclature n'est configurée
     */
    private function createDefaultVersion(Movie $movie): array
    {
        return [
            'type' => 'VO',
            'audio_lang' => 'original',
            'sub_lang' => null,
            'nomenclature' => $movie->title . '_VO'
        ];
    }
    
    /**
     * Supprimer les versions en doublon
     */
    private function deduplicateVersions(array $versions): array
    {
        $unique = [];
        $seen = [];
        
        foreach ($versions as $version) {
            $key = $version['type'] . '_' . ($version['audio_lang'] ?? '') . '_' . ($version['sub_lang'] ?? '');
            
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $version;
            }
        }
        
        return $unique;
    }
    
    /**
     * Supprimer toutes les versions d'un film et les recréer
     */
    public function regenerateVersionsForMovie(Movie $movie, int $festivalId): array
    {
        Log::info("Regenerating versions for movie {$movie->id}");
        
        DB::transaction(function () use ($movie) {
            // Supprimer les versions existantes
            $movie->versions()->delete();
        });
        
        // Générer de nouvelles versions
        return $this->generateVersionsForMovie($movie, $festivalId);
    }
}
