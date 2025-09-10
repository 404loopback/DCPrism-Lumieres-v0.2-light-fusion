# Migration du Système de Paramètres - DCPrism

## Vue d'ensemble

Ce document décrit la migration du système de paramètres de DCPrism vers une architecture plus flexible permettant une meilleure distinction entre :
- **Paramètres globaux** : Créés par les admins, disponibles pour tous les festivals
- **Paramètres système** : Obligatoires et automatiquement assignés
- **Sélection par festival** : Les managers choisissent parmi les paramètres disponibles

## Architecture

### Avant
- Une seule table `parameters`
- Liaison directe via `nomenclatures`
- Confusion entre création et sélection

### Après
- Table `parameters` enrichie (`is_system`, `is_global`)
- Nouvelle table pivot `festival_parameters`
- Séparation claire des responsabilités

## Changements de Base de Données

### Nouveaux champs dans `parameters`
```sql
ALTER TABLE parameters ADD COLUMN is_system BOOLEAN DEFAULT FALSE;
ALTER TABLE parameters ADD COLUMN is_global BOOLEAN DEFAULT TRUE;
```

### Nouvelle table `festival_parameters`
```sql
CREATE TABLE festival_parameters (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    festival_id BIGINT NOT NULL,
    parameter_id BIGINT NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    custom_default_value TEXT NULL,
    custom_formatting_rules JSON NULL,
    display_order INTEGER NULL,
    festival_specific_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (festival_id) REFERENCES festivals(id) ON DELETE CASCADE,
    FOREIGN KEY (parameter_id) REFERENCES parameters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_festival_parameter (festival_id, parameter_id)
);
```

## Modèles et Relations

### Parameter.php - Nouveaux scopes
```php
// Paramètres système (obligatoires)
Parameter::system()->get()

// Paramètres globaux (créés par admin)
Parameter::global()->get()

// Paramètres disponibles pour sélection
Parameter::availableForFestivals()->get()

// Relation avec festivals via festival_parameters
public function festivals(): BelongsToMany
{
    return $this->belongsToMany(Festival::class, 'festival_parameters')
                ->withPivot(['is_enabled', 'custom_default_value', ...])
}
```

### Festival.php - Nouvelles relations
```php
// Tous les paramètres du festival
public function parameters(): BelongsToMany

// Paramètres activés seulement  
public function activeParameters(): BelongsToMany

// Paramètres système du festival
public function systemParameters(): BelongsToMany
```

### FestivalParameter.php - Modèle pivot
```php
// Table pivot enrichie avec méthodes helper
public function getEffectiveDefaultValue()
public function getEffectiveFormattingRules(): array
public function isSystemParameter(): bool
```

## Interfaces Utilisateur

### Interface Admin (/admin/parameters)
- Gestion complète des paramètres globaux
- Marquage des paramètres système
- CRUD complet sur tous les paramètres

### Interface Manager (/panel/parameters)
- Vue des paramètres assignés au festival
- **Bouton "Ajouter des Paramètres"** → Sélection parmi les globaux
- **Bouton "Créer"** → Paramètre personnalisé pour le festival
- Impossible de supprimer les paramètres système

## Workflow

### 1. Admin crée des paramètres globaux
```php
Parameter::create([
    'name' => 'resolution',
    'is_system' => true,  // Obligatoire pour tous
    'is_global' => true,  // Disponible pour sélection
    'is_active' => true
]);
```

### 2. Auto-assignation système
```php
// Observer sur Festival::created()
foreach (Parameter::system()->active()->get() as $param) {
    FestivalParameter::create([
        'festival_id' => $festival->id,
        'parameter_id' => $param->id,
        'is_enabled' => true
    ]);
}
```

### 3. Manager sélectionne des paramètres
- Via interface de sélection : `/panel/parameters/select`
- CheckboxList des paramètres disponibles
- Exclusion des paramètres déjà assignés
- Exclusion des paramètres système (déjà assignés auto)

## Migration des Données

### Script de migration
```bash
# Appliquer les migrations
php artisan migrate

# Assigner paramètres système aux festivals existants  
php artisan festivals:assign-system-parameters

# Test du workflow complet
php artisan test:parameter-workflow
```

### Données existantes
- Migration automatique des `nomenclatures` vers `festival_parameters`
- Marquage automatique des paramètres système
- Conservation de la compatibilité avec l'ancien système

## Commandes Artisan

```bash
# Assigner paramètres système à tous les festivals
php artisan festivals:assign-system-parameters

# Assigner à un festival spécifique
php artisan festivals:assign-system-parameters --festival-id=1

# Tester le système complet
php artisan test:parameter-workflow

# Réinitialiser les données de test
php artisan test:parameter-workflow --reset
```

## Types de Paramètres

### Système (is_system=true)
- **Obligatoires** pour tous les festivals
- **Auto-assignés** à la création du festival
- **Non supprimables** par les managers
- Exemples : title, year, language, resolution

### Globaux (is_global=true, is_system=false)
- **Créés par les admins**
- **Disponibles** pour sélection par les managers
- **Optionnels** pour les festivals
- Exemples : genre, director, distributor

### Personnalisés (is_global=false)
- **Créés par les managers**
- **Spécifiques** à un festival
- **Gestion complète** par le manager

## Personnalisation par Festival

Chaque paramètre assigné à un festival peut avoir :
- **Valeur par défaut personnalisée**
- **Règles de formatage spécifiques**
- **Ordre d'affichage customisé**
- **Notes internes** au festival

## Sécurité et Validation

### Contraintes
- Paramètres système non supprimables
- Unicité festival/paramètre
- Validation des types de données
- Contraintes de clés étrangères

### Permissions
- **Admins** : Gestion complète des paramètres globaux
- **Managers** : Sélection et personnalisation pour leur festival
- **Système** : Auto-assignation transparente

## Tests et Validation

Le système inclut des tests automatisés vérifiant :
- Structure de base de données
- Création et assignation des paramètres  
- Auto-assignation système
- Intégrité des relations
- Workflow complet

```bash
php artisan test:parameter-workflow
```

## Compatibilité

### Rétrocompatibilité
- L'ancien système `nomenclatures` reste fonctionnel
- Migration transparente des données existantes
- Pas de rupture pour les utilisateurs

### Migration progressive
- Nouveau système utilisé pour les nouveaux festivals
- Ancien système maintenu pour les festivals existants
- Migration optionnelle via commandes Artisan
