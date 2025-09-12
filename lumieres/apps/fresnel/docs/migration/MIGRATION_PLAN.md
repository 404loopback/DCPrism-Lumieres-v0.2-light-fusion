# Plan de Migration DCPrism → DCPrism-Laravel/Filament
## Migration Progressive du Système de Print Traffic pour Festivals

**📋 Référence Workflow** : [WORKFLOWS_METIER.md](./WORKFLOWS_METIER.md)

---

## 🏗️ Architecture Actuelle (DCPrism-Laravel)

### ✅ **Ressources Filament Existantes** (TERMINÉ)
<!-- FAIT ✅ --> - `MovieResource` - Gestion des films
<!-- FAIT ✅ --> - `FestivalResource` - Gestion des festivals 
<!-- FAIT ✅ --> - `UserResource` - Gestion des utilisateurs
<!-- FAIT ✅ --> - `ParameterResource` - Système de paramètres
<!-- FAIT ✅ --> - `NomenclatureResource` - Règles de nomenclature
<!-- FAIT ✅ --> - `MovieMetadataResource` - Métadonnées des films
<!-- FAIT ✅ --> - `ValidationResultResource` - Résultats de validation
<!-- FAIT ✅ --> - `ActivityLogResource` - Logs d'activité

### 📊 **Widgets Dashboard** (EN COURS)
<!-- TODO --> - `StatsOverview` - Vue d'ensemble statistiques
<!-- TODO --> - `LatestMovies` - Derniers films
<!-- TODO --> - `DcpStatisticsWidget` - Stats DCP
<!-- TODO --> - `StorageUsageWidget` - Usage du stockage
<!-- TODO --> - `JobMonitoringWidget` - Suivi des tâches

---

## 📋 Plan de Migration par Phases

### **PHASE 1 : Dashboard SuperAdmin & Fondations** 🎬
**Objectif** : Dashboard SuperAdmin complet + Infrastructure de base DCP

#### Workflows Intégrés
- **SuperAdmin** : Gestion globale festivals, supervision
- **Création festivals** : Avec Manager obligatoire
- **Fondations DCP** : Versions, langues, fichiers DCP

#### Tables à Migrer
1. **`versions`** - Versions linguistiques (VO, VOST, DUB, VF)
2. **`langs`** - Langues disponibles  
3. **`dcps`** - Fichiers DCP avec validation

#### Ordre de Migration
```
1. langs (indépendante)
2. versions (dépend de: movies, langs)
3. dcps (dépend de: movies, versions, users)
```

#### Actions
<!-- FAIT ✅ --> - [x] Créer migration `create_langs_table`
<!-- FAIT ✅ --> - [x] Créer migration `create_versions_table` 
<!-- FAIT ✅ --> - [x] Créer migration `create_dcps_table`
<!-- FAIT ✅ --> - [x] Créer modèles Eloquent : `Lang`, `Version`, `Dcp`
<!-- PARTIEL 🟡 --> - [ ] Créer ressources Filament : `LangResource`, `VersionResource`, `DcpResource`
<!-- TODO --> - [ ] Mettre à jour `Movie` model pour relation `versions()`
<!-- TODO --> - [ ] Widget : `DcpVersionsOverviewWidget`

#### Estimation : **2-3 semaines**

---

### **PHASE 2 : Dashboard Manager Festival** 🎪
**Objectif** : Interface Manager pour création films + configuration festivals

#### Workflows Intégrés
- **Configuration festival** : Paramètres, nomenclature personnalisable
- **Création films** : Avec génération automatique versions
- **Gestion comptes** : Création automatique Sources via email
- **Système nomenclature** : Configurable par festival (ordre + champs custom)

#### Tables à Développer
1. **`festival_configs`** - Configuration nomenclature par festival
2. **`nomenclature_rules`** - Règles génération nomenclature
3. **`festival_parameters`** - Paramètres disponibles par festival
4. **`user_sources`** - Comptes Sources créés automatiquement
5. **Relations** : Festival → Manager (obligatoire)

#### Ordre de Migration
```
1. cpls (indépendante)
2. pkls (indépendante) 
3. pkl_cpl (dépend de: cpls, pkls)
4. dcp_pkl (dépend de: dcps, pkls)
5. kdms (dépend de: cpls, screens - PHASE 3)
```

#### Actions
- [ ] Créer migrations pour tables techniques DCP
- [ ] Modèles Eloquent avec relations complexes
- [ ] Ressources Filament pour gestion technique
- [ ] Système de validation DCP automatisé
- [ ] Widget : `DcpTechnicalStatusWidget`

#### Estimation : **3-4 semaines**

---

### **PHASE 3 : Interface Sources & Upload DCP** 📤
**Objectif** : Dashboard Source + Upload multipart vers Backblaze

#### Workflows Intégrés
- **Interface Source** : Sélection versions demandées
- **Upload multipart** : Frontend-only vers Backblaze (un répertoire/version)
- **Intégration serveur externe** : Analyse automatique post-upload
- **Validation DCP** : Rapport conformité + statut VALIDE/NON

#### Tables à Développer
1. **`dcp_parameters`** - Paramètres techniques extraits du DCP
2. **`conformity_reports`** - Rapports de conformité Backblaze
3. **`upload_sessions`** - Suivi uploads multipart
4. **`external_analysis`** - Résultats serveur analyse externe

#### Actions
- [ ] Créer système de gestion des cinémas
- [ ] Interface de programmation des séances
- [ ] Calendrier de projection intégré
- [ ] Gestion des KDM par écran
- [ ] Widget : `CinemaScheduleWidget`, `ScreeningCalendarWidget`

#### Estimation : **2-3 semaines**

---

### **PHASE 4 : Interface Technicien & Validation** 👨‍💻
**Objectif** : Dashboard Technicien + Contrôle qualité DCP

#### Workflows Intégrés
- **Dashboard Technicien** : Validation manuelle des DCP
- **Contrôle qualité** : Interface technique avancée
- **Override validation** : Correction manuelle si nécessaire
- **Workflow complet** : Source → Upload → Analyse → Validation

#### Tables à Migrer
1. **`upload_tokens`** - Tokens d'upload temporaires
2. **`b2_configs`** - Configuration Backblaze par festival

#### Fonctionnalités à Implémenter
- [ ] Système de tokens d'upload sécurisés
- [ ] MFA optionnel (Google Authenticator)
- [ ] Login par PIN pour utilisateurs externes
- [ ] Configuration Backblaze granulaire
- [ ] Middleware d'authentification custom

#### Estimation : **2 semaines**

---

### **PHASE 5 : Intégration Cinémas (Futur)** 🎭
**Objectif** : Base données cinémas + Validation relationnelle DCP/Salle

#### Workflows Intégrés
- **Base données cinémas** : Gestion salles de projection
- **Paramètres salles** : Spécifications techniques par salle
- **Mapping compatibilité** : DCP_parameters ↔ Cinema_parameters
- **Validation relationnelle** : Compatibilité DCP/Salle de diffusion

#### Tables à Développer
1. **`cinemas`** - Salles de cinéma
2. **`cinema_parameters`** - Paramètres techniques salles
3. **`compatibility_rules`** - Règles compatibilité DCP/Salle
4. **`cinema_validations`** - Résultats validation relationnelle

#### Actions
- [ ] Système de notifications en temps réel
- [ ] Calendrier intégré des événements
- [ ] Webhook pour événements externes
- [ ] Timeline d'activité par film/festival
- [ ] Widget : `EventsTimelineWidget`, `NotificationsWidget`

#### Estimation : **2-3 semaines**

---

### **PHASE 6 : Optimisations & Monitoring** 📊
**Objectif** : Performance, monitoring, analytics avancés

#### Fonctionnalités
1. **Analytics avancés** - Métriques usage, performance
2. **Monitoring uploads** - Suivi temps réel des transferts
3. **Alertes système** - Notifications pannes/problèmes
4. **Rapports festivals** - Statistiques détaillées par festival

#### Actions
- [ ] Gestion des contrats et facturation
- [ ] Upload et gestion des assets visuels
- [ ] Système de thème par festival
- [ ] Rapports financiers
- [ ] Widget : `ContractsOverviewWidget`, `RevenueWidget`

#### Estimation : **1-2 semaines**

---

## 🔄 Stratégies de Migration

### **Migration des Données**
```bash
# 1. Export depuis SQLite ancien
php artisan migrate:export-from-sqlite /path/to/old/database.sqlite

# 2. Transformation et nettoyage
php artisan migrate:transform-data

# 3. Import progressif par phase
php artisan migrate:import-phase --phase=1
```

### **Compatibilité Ascendante**
- [ ] API endpoints compatibles avec ancien système
- [ ] Alias de tables pour transition douce
- [ ] Middleware de transformation des données

### **Tests de Migration**
```bash
# Tests automatisés pour chaque phase
php artisan test --filter=MigrationPhase1Test
php artisan test --filter=DataIntegrityTest
```

---

## 🎯 Priorités et Dépendances

### **Critiques** (Phase 1)
1. `versions` → Impact sur la gestion des films existants
2. `dcps` → Cœur du système de validation

### **Important** (Phases 2-3)  
1. Tables techniques DCP → Workflow complet
2. Gestion cinémas → Expansion du système

### **Nice-to-have** (Phases 4-6)
1. Authentification avancée → UX améliorée
2. Événements/Commercial → Fonctionnalités bonus

---

## 📊 Planning Prévisionnel

| Phase | Durée | Début | Fin | Jalons |
|-------|-------|--------|-----|---------|
| Phase 1 | 3 sem | S1 | S3 | ✅ Versions + DCP base |
| Phase 2 | 4 sem | S4 | S7 | ✅ Workflow DCP complet |
| Phase 3 | 3 sem | S8 | S10 | ✅ Cinémas + Projections |
| Phase 4 | 2 sem | S11 | S12 | ✅ Auth avancée |
| Phase 5 | 3 sem | S13 | S15 | ✅ Événements |
| Phase 6 | 2 sem | S16 | S17 | ✅ Commercial |

**Total Estimation : 17 semaines (~4 mois)**

---

## 🚀 Commandes de Migration

### **Génération des Migrations**
```bash
# Phase 1
php artisan make:migration create_langs_table
php artisan make:migration create_versions_table  
php artisan make:migration create_dcps_table

# Phase 2
php artisan make:migration create_cpls_table
php artisan make:migration create_pkls_table
# ... etc
```

### **Génération des Ressources Filament**
```bash
# Avec Relations
php artisan make:filament-resource Version --generate --view

# Widgets personnalisés
php artisan make:filament-widget DcpVersionsOverviewWidget --stats-overview
```

---

## ⚠️ Points d'Attention

### **Compatibilité**
- [ ] Vérifier les foreign keys entre anciennes/nouvelles tables
- [ ] Maintenir les ID existants lors de la migration
- [ ] Tests de régression après chaque phase

### **Performance**  
- [ ] Index sur les nouvelles tables importantes
- [ ] Cache des relations complexes (DCP ↔ CPL ↔ PKL)
- [ ] Pagination des gros datasets

### **Sécurité**
- [ ] Validation des uploads DCP
- [ ] Permissions granulaires sur nouvelles ressources
- [ ] Audit trail des modifications

---

## 📝 Checklist par Phase

### **Phase 1 - Fondations DCP** (✅ 60% ACCOMPLI)
<!-- FAIT ✅ --> - [x] Migration `langs` créée et testée
<!-- FAIT ✅ --> - [x] Migration `versions` créée et testée  
<!-- FAIT ✅ --> - [x] Migration `dcps` créée et testée
<!-- FAIT ✅ --> - [x] Modèles avec relations configurées
<!-- PARTIEL 🟡 --> - [ ] Ressources Filament fonctionnelles
<!-- TODO --> - [ ] Widget dashboard ajouté
<!-- TODO --> - [ ] Tests unitaires passants
<!-- TODO --> - [ ] Documentation mise à jour

---

*Ce plan de migration sera mis à jour au fur et à mesure de l'avancement du projet.*
