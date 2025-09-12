# 📚 Guides Utilisateur DCPrism - Résumés par Rôle

Collection de guides rapides pour tous les rôles utilisateur DCPrism

---

## 🎪 **Manager Festival** - `/panel/manager`

### 🎯 **Votre Mission**
Gérer les films de votre festival et coordonner avec les sources pour l'upload des DCPs.

### ⚡ **Actions Principales**

#### 1. Sélectionner Votre Festival
- **Première connexion** → Page sélection automatique
- **Changement** → Menu utilisateur → "Changer de festival"
- ⚠️ **Obligation** : Vous devez sélectionner un festival pour accéder aux fonctions

#### 2. Créer un Film
1. **Films** → **Nouveau Film**
2. **Informations requises** :
   - Titre du film
   - Email de la source (producteur/distributeur)
   - Versions attendues (VF, VOST, VO, etc.)
   - Format et genre

3. **Génération automatique** :
   - ✅ Nomenclature selon règles festival
   - ✅ Création versions linguistiques
   - ✅ Envoi notification à la source

#### 3. Gestion des Sources
- **Auto-création** : Comptes sources créés automatiquement par email
- **Suivi** : Statut des uploads par film
- **Communication** : Messages automatiques de relance

#### 4. Suivi des DCPs
- **Dashboard** : Vue d'ensemble validation technique
- **Statuts** : En attente → Téléchargé → Validé → Prêt
- **Actions** : Téléchargement DCPs validés

### 🔍 **Indicateurs Clés**
- Films créés vs DCPs reçus
- Taux de validation technique
- Délais de livraison par source

---

## 📤 **Source (Producteur/Distributeur)** - `/panel/source`

### 🎯 **Votre Mission**
Uploader les DCPs de vos films selon les versions demandées par les festivals.

### ⚡ **Actions Principales**

#### 1. Vue d'Ensemble
- **Dashboard** → Films assignés par festival
- **Statuts** : À uploader, En cours, Terminé, Validé
- **Progression** : Barre de progression temps réel

#### 2. Upload DCP
1. **Sélectionner le film** dans votre liste
2. **Choisir la version** (VF, VOST, etc.)
3. **Télécharger le DCP** :
   - Formats : ZIP, TAR, dossier compressé
   - Taille max : 4GB par fichier
   - **Progression temps réel** visible

4. **Suivi automatique** :
   - Découpage en chunks multipart
   - Upload vers Backblaze B2
   - Analyse technique automatique

#### 3. Gestion des Erreurs
- **Échec upload** → Reprise automatique possible
- **DCP invalide** → Notification + détails techniques
- **Re-upload** → Nouvelle version si rejet

#### 4. Communication
- **Notifications email** : Confirmations et demandes
- **Messages** : Communication directe avec Manager festival
- **Historique** : Toutes actions tracées

### 🔍 **Indicateurs Clés**
- DCPs uploadés vs attendus
- Taux de validation première fois
- Temps moyen d'upload

---

## 🔧 **Technicien Validation** - `/panel/tech`

### 🎯 **Votre Mission**
Valider techniquement les DCPs reçus pour s'assurer de leur conformité cinéma.

### ⚡ **Actions Principales**

#### 1. Dashboard Validation
- **Queue DCPs** : Liste prioritaire à valider
- **Filtres** : Par festival, statut, urgence
- **Métriques** : Temps moyen, taux validation

#### 2. Validation Individuelle
1. **Ouvrir un DCP** depuis la liste
2. **Analyse automatique** déjà effectuée :
   - Métadonnées techniques
   - Structure fichiers
   - Conformité DCI/SMPTE

3. **Validation manuelle** :
   - ✅ **Valider** → DCP prêt pour diffusion
   - ❌ **Rejeter** → Retour source avec notes
   - 📝 **Notes techniques** obligatoires si rejet

#### 3. Actions Groupées
- **Sélection multiple** → Validation/rejet en masse
- **Filtres avancés** → Par critères techniques
- **Export** → Rapports pour festival

#### 4. Contrôle Qualité
- **Tests lecture** : Vérification décodage
- **Checksums** : Intégrité fichiers
- **Métadonnées** : Cohérence durée, format, langues
- **Encryption** : Gestion KDM si chiffré

### 🔍 **Indicateurs Clés**
- DCPs validés par jour
- Taux de rejet par type d'erreur  
- Temps moyen de validation

---

## 🎭 **Cinema (Exploitation)** - `/panel/cinema`

### 🎯 **Votre Mission**
Télécharger les DCPs validés pour projection dans vos salles.

### ⚡ **Actions Principales**

#### 1. Catalogue Disponible
- **DCPs validés** par festival
- **Filtres** : Genre, durée, langue, format
- **Recherche** : Par titre ou festival

#### 2. Téléchargement
1. **Sélectionner DCP** validé
2. **Vérifier compatibilité** avec votre équipement
3. **Télécharger** :
   - Lien sécurisé temporaire
   - Checksums pour vérification
   - Notice technique incluse

#### 3. Gestion Projections
- **Planning** : Association DCP ↔ Séances
- **KDM** : Clés de déchiffrement si nécessaire
- **Historique** : Téléchargements et projections

### 🔍 **Indicateurs Clés**
- DCPs téléchargés vs programmés
- Succès projections techniques
- Diversité catalogue utilisé

---

## 👀 **Supervisor** - `/panel/supervisor`

### 🎯 **Votre Mission**
Supervision globale des activités sans pouvoir de modification.

### ⚡ **Actions Principales**

#### 1. Vue d'Ensemble
- **Métriques globales** : Films, DCPs, validations
- **Performance** : Temps traitement, taux succès
- **Activité temps réel** : Uploads en cours, validations

#### 2. Monitoring
- **Festivals actifs** : Statistiques par festival
- **Sources productives** : Classement par volume
- **Techniciens** : Charge de travail, performance
- **Système** : Queues, stockage, erreurs

#### 3. Rapports
- **Dashboards** : Métriques business
- **Exports** : Données pour analyse externe  
- **Alertes** : Notifications problèmes système

### 🔍 **Indicateurs Clés**
- Vue 360° de l'activité plateforme
- Détection proactive des goulets
- Optimisation des workflows

---

## 📱 **Connexion et Navigation**

### URLs d'Accès
- **SuperAdmin** : `/panel/admin`
- **Manager** : `/panel/manager`  
- **Source** : `/panel/source`
- **Technicien** : `/panel/tech`
- **Cinema** : `/panel/cinema`
- **Supervisor** : `/panel/supervisor`

### Authentification
- **Email** : Votre adresse email (identifiant unique)
- **Mot de passe** : Défini lors création compte ou réinitialisation
- **2FA** : Recommandé pour tous les rôles

### Navigation
- **Menu principal** : Gauche, adapté à votre rôle
- **Breadcrumbs** : Fil d'Ariane en haut de page
- **Recherche globale** : Barre de recherche universelle
- **Notifications** : Cloche en haut à droite
- **Profil** : Menu utilisateur pour paramètres

---

## 🆘 **Support Utilisateur**

### Auto-Assistance
- **Tooltips** : Info-bulles sur les champs complexes
- **Notifications** : Messages contextuels d'aide
- **Status** : Indicateurs visuels de progression

### Contacts
- **Support technique** : support@dcprism.com
- **Formation** : training@dcprism.com  
- **Urgent** : Via système de notifications intégré

### Ressources
- **FAQ** : Questions fréquentes par rôle
- **Tutoriels vidéo** : Workflows principaux
- **Documentation** : Guides détaillés par fonction

---

*Guides Utilisateur DCPrism - Tous Rôles*  
*Version 1.0 - 1er septembre 2025*
