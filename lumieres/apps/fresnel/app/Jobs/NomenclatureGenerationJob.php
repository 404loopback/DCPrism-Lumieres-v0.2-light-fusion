<?php

namespace App\Jobs;

use App\Models\Movie;
use App\Services\NomenclatureService;

class NomenclatureGenerationJob extends BaseDcpJob
{
    protected NomenclatureService $nomenclatureService;
    protected array $nomenclatureRules;

    public function __construct(Movie $movie, array $options = [], array $nomenclatureRules = [])
    {
        parent::__construct($movie, $options);
        $this->nomenclatureRules = $nomenclatureRules;
        $this->nomenclatureService = app(NomenclatureService::class);
    }

    protected function getJobType(): string
    {
        return 'nomenclature';
    }

    protected function process(): array
    {
        $results = [
            'success' => true,
            'message' => 'Génération de nomenclature complétée',
            'generated_names' => [],
            'rules_applied' => [],
            'conflicts' => [],
        ];

        try {
            $this->updateProgress(25, 'Chargement des règles de nomenclature...');
            $rules = $this->loadNomenclatureRules();

            $this->updateProgress(50, 'Application des règles...');
            $results['generated_names'] = $this->generateNames($rules);
            $results['rules_applied'] = $rules;

            $this->updateProgress(75, 'Vérification des conflits...');
            $results['conflicts'] = $this->checkConflicts($results['generated_names']);

            $this->updateProgress(90, 'Mise à jour de la nomenclature...');
            $this->updateMovieNomenclature($results['generated_names']);

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['message'] = 'Erreur lors de la génération: ' . $e->getMessage();
        }

        return $results;
    }

    protected function loadNomenclatureRules(): array
    {
        if (!empty($this->nomenclatureRules)) {
            return $this->nomenclatureRules;
        }

        // Charger les règles du festival
        if ($this->movie->festival && $this->movie->festival->nomenclature_rules) {
            return $this->movie->festival->nomenclature_rules;
        }

        // Règles par défaut
        return [
            'format' => '{title}_{festival}_{version}_{date}',
            'separator' => '_',
            'case' => 'upper',
            'max_length' => 50,
            'remove_accents' => true,
            'remove_spaces' => true,
        ];
    }

    protected function generateNames(array $rules): array
    {
        $baseData = [
            'title' => $this->sanitizeForNomenclature($this->movie->title),
            'festival' => $this->movie->festival ? $this->sanitizeForNomenclature($this->movie->festival->name) : 'GENERIC',
            'version' => $this->movie->version ?? 'V1',
            'date' => now()->format('Ymd'),
            'resolution' => '2K',
            'fps' => '24',
            'lang' => 'FR',
        ];

        $generated = [
            'dcp_folder' => $this->applyNomenclatureTemplate($rules['format'] ?? '{title}_{festival}_{version}', $baseData),
            'cpl_name' => $this->applyNomenclatureTemplate('{title}_{version}_CPL', $baseData),
            'pkl_name' => $this->applyNomenclatureTemplate('{title}_{version}_PKL', $baseData),
            'assetmap_name' => 'ASSETMAP',
        ];

        // Appliquer les transformations
        if ($rules['case'] === 'upper') {
            $generated = array_map('strtoupper', $generated);
        } elseif ($rules['case'] === 'lower') {
            $generated = array_map('strtolower', $generated);
        }

        // Limiter la longueur
        $maxLength = $rules['max_length'] ?? 50;
        foreach ($generated as $key => $name) {
            if (strlen($name) > $maxLength) {
                $generated[$key] = substr($name, 0, $maxLength);
            }
        }

        return $generated;
    }

    protected function applyNomenclatureTemplate(string $template, array $data): string
    {
        $result = $template;
        foreach ($data as $key => $value) {
            $result = str_replace('{' . $key . '}', $value, $result);
        }
        return $result;
    }

    protected function sanitizeForNomenclature(string $text): string
    {
        // Supprimer les accents
        $text = $this->removeAccents($text);
        
        // Remplacer espaces et caractères spéciaux
        $text = preg_replace('/[^a-zA-Z0-9]/', '_', $text);
        
        // Supprimer les underscores multiples
        $text = preg_replace('/_+/', '_', $text);
        
        // Supprimer les underscores en début/fin
        return trim($text, '_');
    }

    protected function removeAccents(string $text): string
    {
        $accents = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c',
        ];
        
        return strtr($text, $accents);
    }

    protected function checkConflicts(array $generatedNames): array
    {
        $conflicts = [];
        
        // Vérifier si des noms similaires existent déjà
        foreach ($generatedNames as $type => $name) {
            $existing = Movie::where('title', 'LIKE', '%' . substr($name, 0, 20) . '%')
                ->where('id', '!=', $this->movie->id)
                ->count();
                
            if ($existing > 0) {
                $conflicts[] = [
                    'type' => $type,
                    'name' => $name,
                    'conflicts' => $existing,
                    'suggestion' => $name . '_' . now()->format('His'),
                ];
            }
        }
        
        return $conflicts;
    }

    protected function updateMovieNomenclature(array $generatedNames): void
    {
        $nomenclature = array_merge(
            $this->movie->nomenclature ?? [],
            $generatedNames
        );
        
        $this->movie->update([
            'nomenclature' => $nomenclature
        ]);
    }
}
