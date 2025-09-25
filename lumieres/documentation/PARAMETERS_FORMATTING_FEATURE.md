# Colonne de Formatage des Paramètres - DCPrism

## Vue d'ensemble

Ce document détaille l'ajout de la **colonne de formatage** dans la table `parameters` de l'application DCPrism. Cette fonctionnalité permet d'appliquer des règles de formatage automatiques aux valeurs des paramètres dans le système de nomenclature.

## Structure de la Base de Données

### Migration ajoutée

```php
Schema::table('parameters', function (Blueprint $table) {
    $table->json('formatting_rules')->nullable()->after('validation_rules');
});
```

### Description du champ

- **Nom** : `formatting_rules`
- **Type** : JSON
- **Nullable** : Oui
- **Position** : Après `validation_rules`

## Utilisation des Règles de Formatage

### Format JSON

Le champ `formatting_rules` stocke un objet JSON contenant les règles de transformation :

```json
{
    "case": "uppercase",
    "trim": true,
    "replace": {
        " ": "_",
        "-": "_"
    },
    "prefix": "",
    "suffix": "",
    "max_length": 50,
    "remove_accents": true
}
```

### Règles Disponibles

#### 1. **Transformation de casse**
- `"case": "uppercase"` : Convertit en majuscules
- `"case": "lowercase"` : Convertit en minuscules
- `"case": "title"` : Première lettre de chaque mot en majuscule
- `"case": "sentence"` : Première lettre seulement en majuscule

#### 2. **Nettoyage**
- `"trim": true` : Supprime les espaces en début/fin
- `"remove_accents": true` : Supprime les accents (é → e, à → a, etc.)

#### 3. **Remplacement**
```json
"replace": {
    "chaîne_à_remplacer": "chaîne_de_remplacement",
    " ": "_",
    "-": "_"
}
```

#### 4. **Préfixes et Suffixes**
- `"prefix": "DCP_"` : Ajoute un préfixe
- `"suffix": "_HD"` : Ajoute un suffixe

#### 5. **Limitation de longueur**
- `"max_length": 50` : Tronque à 50 caractères

## Exemples d'Utilisation

### Paramètre "Titre du Film"

```json
{
    "case": "uppercase",
    "trim": true,
    "replace": {
        " ": "_",
        "'": "",
        "\"": ""
    },
    "remove_accents": true,
    "max_length": 30
}
```

**Entrée** : `"Les Parapluies de Cherbourg"`  
**Sortie** : `"LES_PARAPLUIES_DE_CHERBOURG"`

### Paramètre "Code DCP"

```json
{
    "case": "uppercase",
    "trim": true,
    "prefix": "DCP_",
    "suffix": "_2024",
    "replace": {
        " ": "_"
    }
}
```

**Entrée** : `"film test"`  
**Sortie** : `"DCP_FILM_TEST_2024"`

### Paramètre "Format"

```json
{
    "case": "uppercase",
    "replace": {
        "2k": "2K",
        "4k": "4K",
        "hd": "HD"
    }
}
```

## Implémentation dans le Code

### 1. Modèle Parameter

```php
class Parameter extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'category',
        'validation_rules', 'formatting_rules', // ← Nouveau champ
        'default_value', 'description'
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'formatting_rules' => 'array', // ← Cast automatique en array
        'is_required' => 'boolean',
        'is_system' => 'boolean'
    ];

    /**
     * Applique les règles de formatage à une valeur
     */
    public function formatValue(string $value): string
    {
        if (!$this->formatting_rules) {
            return $value;
        }

        $formatted = $value;
        $rules = $this->formatting_rules;

        // 1. Trim
        if ($rules['trim'] ?? false) {
            $formatted = trim($formatted);
        }

        // 2. Suppression des accents
        if ($rules['remove_accents'] ?? false) {
            $formatted = $this->removeAccents($formatted);
        }

        // 3. Remplacement
        if (!empty($rules['replace'])) {
            foreach ($rules['replace'] as $search => $replace) {
                $formatted = str_replace($search, $replace, $formatted);
            }
        }

        // 4. Transformation de casse
        if (!empty($rules['case'])) {
            $formatted = match($rules['case']) {
                'uppercase' => strtoupper($formatted),
                'lowercase' => strtolower($formatted),
                'title' => ucwords($formatted),
                'sentence' => ucfirst(strtolower($formatted)),
                default => $formatted
            };
        }

        // 5. Préfixe/Suffixe
        if (!empty($rules['prefix'])) {
            $formatted = $rules['prefix'] . $formatted;
        }
        if (!empty($rules['suffix'])) {
            $formatted = $formatted . $rules['suffix'];
        }

        // 6. Limitation de longueur
        if (!empty($rules['max_length'])) {
            $formatted = substr($formatted, 0, $rules['max_length']);
        }

        return $formatted;
    }

    private function removeAccents(string $string): string
    {
        $accents = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            // ... autres caractères
        ];

        return strtr($string, $accents);
    }
}
```

### 2. Service de Nomenclature

```php
class NomenclatureService
{
    public function generateFilename(Movie $movie, array $parameterValues): string
    {
        $parts = [];
        
        $nomenclature = $movie->festival->nomenclature()
            ->orderBy('order_position')
            ->get();

        foreach ($nomenclature as $rule) {
            $parameter = $rule->parameter;
            $rawValue = $parameterValues[$parameter->code] ?? $parameter->default_value;
            
            if ($rawValue) {
                // Application des règles de formatage du paramètre
                $formattedValue = $parameter->formatValue($rawValue);
                
                // Application des règles spécifiques de nomenclature
                if ($rule->prefix) {
                    $formattedValue = $rule->prefix . $formattedValue;
                }
                if ($rule->suffix) {
                    $formattedValue = $formattedValue . $rule->suffix;
                }
                
                $parts[] = $formattedValue;
            }
        }

        return implode('_', $parts);
    }
}
```

### 3. Interface Admin - Formulaire Parameter

```php
// Dans ParameterResource.php - form()
KeyValue::make('formatting_rules')
    ->label('Règles de Formatage')
    ->keyLabel('Règle')
    ->valueLabel('Valeur')
    ->helperText('Règles de transformation automatique des valeurs')
    ->columnSpanFull()
    ->schema([
        // Suggestions prédéfinies
    ])
```

## Tests de Validation

### Tests Unitaires

```php
class ParameterFormattingTest extends TestCase
{
    public function test_formatting_uppercase()
    {
        $parameter = Parameter::factory()->create([
            'formatting_rules' => ['case' => 'uppercase']
        ]);

        $result = $parameter->formatValue('hello world');
        $this->assertEquals('HELLO WORLD', $result);
    }

    public function test_formatting_replace_spaces()
    {
        $parameter = Parameter::factory()->create([
            'formatting_rules' => [
                'replace' => [' ' => '_']
            ]
        ]);

        $result = $parameter->formatValue('hello world');
        $this->assertEquals('hello_world', $result);
    }

    public function test_formatting_complex()
    {
        $parameter = Parameter::factory()->create([
            'formatting_rules' => [
                'case' => 'uppercase',
                'trim' => true,
                'replace' => [' ' => '_'],
                'prefix' => 'DCP_',
                'max_length' => 20
            ]
        ]);

        $result = $parameter->formatValue('  hello world  ');
        $this->assertEquals('DCP_HELLO_WORLD', $result);
    }
}
```

## Cas d'Usage Métier

### 1. **Standardisation des Noms de Fichiers DCP**
Les règles de formatage garantissent que tous les noms de fichiers respectent les conventions :
- Majuscules uniquement
- Espaces remplacés par underscores
- Caractères spéciaux supprimés

### 2. **Conformité aux Standards de l'Industrie**
Respect des normes DCI (Digital Cinema Initiative) pour le nommage des DCPs.

### 3. **Cohérence Multi-Festival**
Même si chaque festival peut avoir ses propres règles de nomenclature, les règles de formatage de base assurent une cohérence.

## Évolutions Futures

### Règles Avancées Envisagées

1. **Validation de format** : Vérifier que la valeur respecte un pattern
2. **Transformation de dates** : Formatage automatique des dates
3. **Génération de codes** : Création automatique de codes uniques
4. **Intégration API** : Récupération de données depuis des services externes

### Interface Utilisateur

1. **Assistant de création** : Interface graphique pour créer les règles
2. **Prévisualisation** : Aperçu en temps réel du formatage
3. **Templates** : Règles prédéfinies pour différents types de paramètres

---

**Date de création** : 23/09/2024  
**Dernière mise à jour** : 23/09/2024  
**Version** : 1.0
