# ANALYSE DES COMPOSANTS LEGACY - MIGRATION VERS WIZARD

> **ATTENTION**: Cette liste a été générée après une analyse approfondie des dépendances. Chaque composant doit être vérifié individuellement avant suppression.

## 🔍 **MÉTHODOLOGIE D'ANALYSE**

✅ Routes web traditionnelles vérifiées - **AUCUNE TROUVÉE**  
✅ Contrôleurs HTTP traditionnels vérifiés - **AUCUN TROUVÉ**  
✅ Templates Blade dédiés vérifiés - **AUCUN TROUVÉ**  
✅ Assets frontend vérifiés - **AUCUN TROUVÉ**  
✅ Références croisées analysées  
✅ Configuration des panels Filament analysée  

---

## 🔴 **COMPOSANTS POTENTIELLEMENT SUPPRIMABLES**

### **1. RESSOURCES MOVIERESOURCE REDONDANTES**

#### **🟡 ANALYSE REQUISE - MovieResource Admin Panel**
- **Fichier**: `app/Filament/Resources/Movies/MovieResource.php`
- **Status**: ⚠️ **UTILISÉ DANS ADMIN PANEL**
- **Référencé dans**: `app/Providers/Filament/AdminPanelProvider.php:61`
- **Justification**: Ressource générique sans wizard, utilisée par le panel admin
- **Action recommandée**: Migrer vers le wizard ou garder pour l'admin

#### **📄 Pages Associées au MovieResource Admin**
- `app/Filament/Resources/Movies/Pages/CreateMovie.php` ⚠️ **RÉFÉRENCÉ**
- `app/Filament/Resources/Movies/Pages/EditMovie.php` 
- `app/Filament/Resources/Movies/Pages/ListMovies.php`
- `app/Filament/Resources/Movies/Pages/ViewMovie.php`

#### **🟡 ANALYSE REQUISE - MovieResource Festival**
- **Fichier**: `app/Filament/Festival/Resources/Movies/MovieResource.php`
- **Status**: ⚠️ **POTENTIELLEMENT UTILISÉ** (découverte automatique)
- **Panel**: Festival (non configuré mais références trouvées)
- **Justification**: Système avec upload et nomenclature avancés

#### **📄 Pages Associées au MovieResource Festival**
- `app/Filament/Festival/Resources/Movies/Pages/CreateMovie.php` ⚠️ **RÉFÉRENCÉ**
- `app/Filament/Festival/Resources/Movies/Pages/EditMovie.php`
- `app/Filament/Festival/Resources/Movies/Pages/ListMovies.php`

### **2. SCHÉMAS DE FORMULAIRES ANCIENS**

#### **🟡 ANALYSE APPROFONDIE REQUISE - MovieForm Admin**
- **Fichier**: `app/Filament/Resources/Movies/Schemas/MovieForm.php`
- **Status**: ⚠️ **CONTIENT LOGIQUE UTILE**
- **Contient**: 
  - Méthodes `updateVersionName()` et `generateVersionTypeName()`
  - Logique de génération de versions
  - Système de repeater pour versions
- **Action recommandée**: **EXTRAIRE LES MÉTHODES UTILES AVANT SUPPRESSION**

#### **🔴 ATTENTION MAXIMALE - MovieForm Festival**
- **Fichier**: `app/Filament/Festival/Resources/Movies/Schemas/MovieForm.php`
- **Status**: 🚨 **CONTIENT SERVICES CRITIQUES**
- **Contient**:
  - Intégration `BackblazeService` pour upload
  - Intégration `UnifiedNomenclatureService`
  - Logique de prévisualisation nomenclature
  - Gestion d'upload avec progression
- **Action recommandée**: **NE PAS SUPPRIMER SANS MIGRATION COMPLÈTE**

### **3. COMPOSANTS DE TABLES**

#### **🟡 VÉRIFICATION REQUISE**
- **Fichier**: `app/Filament/Resources/Movies/Tables/MoviesTable.php`
- **Status**: Possiblement dupliquée avec les nouvelles tables
- **Contient**: Logique avancée de filtrage et colonnes personnalisées

#### **🟡 VÉRIFICATION REQUISE**
- **Fichier**: `app/Filament/Festival/Resources/Movies/Tables/MoviesTable.php`
- **Status**: Non analysée, possiblement spécialisée

### **4. SCHÉMAS D'INFORMATIONS**

#### **🟡 VÉRIFICATION REQUISE**
- **Fichier**: `app/Filament/Resources/Movies/Schemas/MovieInfolist.php`
- **Status**: Usage incertain, peut être utilisée pour les vues détaillées

---

## 🟢 **COMPOSANTS À CONSERVER (CONFIRMÉS ACTIFS)**

### **✅ SYSTÈME ACTUEL AVEC WIZARD**
- `app/Filament/Manager/Resources/MovieResource.php` ✅ **WIZARD COMPLET**
- `app/Filament/Manager/Resources/MovieResource/Pages/CreateMovie.php` ✅ **PAGE WIZARD**

### **✅ RESSOURCES SPÉCIALISÉES ACTIVES**
- `app/Filament/Source/Resources/MovieResource.php` ✅ **PANEL SOURCE ACTIF**
- `app/Filament/Tech/Resources/MovieResource.php` ✅ **PANEL TECH ACTIF**

### **✅ VUES BLADE ACTIVES**
- `resources/views/filament/manager/widgets/movies-cards.blade.php` ✅
- `resources/views/filament/manager/movies/cards-view.blade.php` ✅

---

## 📋 **PLAN D'ACTION RECOMMANDÉ**

### **PHASE 1: VÉRIFICATION DES DÉPENDANCES CRITIQUES**
1. **Vérifier usage du Panel Festival**
   ```bash
   # Vérifier si le panel Festival est configuré quelque part
   grep -r "FestivalPanelProvider" app/
   ```

2. **Analyser les imports des services critiques**
   ```bash
   # Vérifier l'usage de BackblazeService et UnifiedNomenclatureService
   grep -r "BackblazeService\|UnifiedNomenclatureService" app/
   ```

3. **Tester les panels existants**
   - Vérifier `/panel/admin` utilise bien les ressources Admin
   - Vérifier `/panel/manager` utilise le wizard
   - Vérifier `/panel/source` et `/panel/tech` fonctionnent

### **PHASE 2: EXTRACTION DES FONCTIONNALITÉS UTILES**

#### **🔧 Méthodes à extraire de MovieForm Admin**
```php
// À déplacer vers un Service ou Trait
- updateVersionName()
- generateVersionTypeName() 
- getConditionalParametersSections()
```

#### **🔧 Services à migrer de MovieForm Festival**
```php
// À intégrer dans le wizard Manager
- Logique BackblazeService upload
- Prévisualisation nomenclature
- Gestion de progression upload
```

### **PHASE 3: SUPPRESSION PROGRESSIVE**

#### **🟢 SUPPRESSION SÛRE (après extraction)**
1. `app/Filament/Resources/Movies/Pages/CreateMovie.php`
2. `app/Filament/Festival/Resources/Movies/Pages/CreateMovie.php`
3. Les schémas MovieForm (après extraction des méthodes utiles)

#### **🟡 SUPPRESSION À VALIDER**
1. Ressources MovieResource complètes (après tests panels)
2. Tables redondantes (après vérification usage)
3. Pages EditMovie/ListMovies non utilisées

#### **🔴 NE PAS SUPPRIMER SANS VALIDATION**
1. MovieForm Festival (services critiques)
2. Tout composant avec BackblazeService ou UnifiedNomenclatureService
3. Composants référencés dans les panels actifs

---

## ⚠️ **AVERTISSEMENTS CRITIQUES**

### **🚨 SERVICES CRITIQUES IDENTIFIÉS**
- **BackblazeService**: Gestion upload cloud
- **UnifiedNomenclatureService**: Génération nomenclatures
- **VersionGenerationService**: Création versions automatique

### **🔍 PANELS À VÉRIFIER**
- **Admin Panel**: Utilise `app/Filament/Resources/Movies/MovieResource.php`
- **Manager Panel**: Utilise découverte automatique (wizard)
- **Source Panel**: Utilise découverte automatique
- **Tech Panel**: Utilise découverte automatique
- **Festival Panel**: ⚠️ **STATUT INCERTAIN**

### **📝 TESTS REQUIS AVANT SUPPRESSION**
1. Tester création de film via wizard Manager ✅
2. Tester affichage films dans Admin Panel ⚠️
3. Tester panels Source et Tech ⚠️
4. Vérifier fonctionnement upload DCP ⚠️
5. Vérifier génération nomenclatures ⚠️

---

## 🎯 **RÉSUMÉ EXÉCUTIF**

**Composants identifiés**: 15+ fichiers  
**Suppression directe sûre**: 0 composants  
**Suppression après extraction**: 4-6 composants  
**Analyse approfondie requise**: 8-10 composants  

**Risque global**: 🟡 **MOYEN** (services critiques identifiés)  
**Temps estimé d'analyse complète**: 4-6 heures  
**Temps estimé de migration sécurisée**: 8-12 heures  

---

*Rapport généré le: $(date)*  
*Dernière vérification: Phase 1 - Vérification des dépendances*
