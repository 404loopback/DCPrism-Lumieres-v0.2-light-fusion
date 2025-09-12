# 🎓 Tutoriels DCPrism - Workflows Étape par Étape

Guide pas à pas des processus principaux pour chaque rôle utilisateur

---

## 🎪 **Tutoriel Manager Festival**

### **Workflow Complet : De la Création d'un Film au Téléchargement des DCPs**

#### **Étape 1 : Connexion et Sélection Festival**
1. 🌐 Connectez-vous à `/panel/manager`
2. ➡️ **Si première connexion** : Sélectionnez votre festival dans la liste
3. ➡️ **Si changement** : Menu utilisateur (coin haut-droit) → "Changer de festival"
4. ✅ **Validation** : Le nom du festival apparaît dans l'en-tête

#### **Étape 2 : Création d'un Nouveau Film**
1. 📋 **Navigation** : Menu gauche → "Films" → Bouton "Nouveau Film"
2. 📝 **Informations obligatoires** :
   ```
   • Titre du film : "Le Fabuleux Destin d'Amélie Poulain"
   • Email source : "producteur@studiocanal.com"  
   • Genre : "Comédie romantique"
   • Durée estimée : "122 minutes"
   ```
3. 🎭 **Versions linguistiques** (cochez selon besoins) :
   - ☑️ VF (Version Française)
   - ☑️ VOST (Version Originale Sous-Titrée)
   - ☐ VO (Version Originale)
   - ☐ VostFr (VO + Sous-titres français)

4. 📐 **Format technique** :
   - Résolution : "2K DCI (2048x1080)"  
   - Frame rate : "24 fps"

5. 💾 **Sauvegarde** : Cliquez "Créer le film"

#### **Étape 3 : Vérification de la Génération Automatique**
1. ✅ **Nomenclature générée** automatiquement :
   ```
   CANNES_AMELIE_POULAIN_VF_2K_20250315
   CANNES_AMELIE_POULAIN_VOST_2K_20250315
   ```
2. 📧 **Email automatique envoyé** à producteur@studiocanal.com
3. 👤 **Compte source créé** automatiquement (si n'existe pas)

#### **Étape 4 : Suivi des Uploads**
1. 📊 **Dashboard** → Section "Films Actifs"
2. 🔍 **Indicateurs visuels** :
   ```
   Amélie Poulain
   ├── VF: 🔄 En attente    (0/1 uploaded)
   ├── VOST: 🔄 En attente  (0/1 uploaded)
   └── Progression globale: [░░░░░░░░░░] 0%
   ```

#### **Étape 5 : Gestion des Relances**
1. ⏰ **Si pas d'upload après 48h** :
   - Allez dans "Sources" → Sélectionnez la source
   - Cliquez "Actions" → "Envoyer Rappel"
   - Email automatique avec liens directs

#### **Étape 6 : Validation et Téléchargement**
1. 🔔 **Notification** quand DCP uploadé :
   ```
   ✅ DCP reçu: CANNES_AMELIE_POULAIN_VF_2K_20250315
   📋 Statut: En cours de validation technique
   ```

2. 📞 **Attente validation** (24-48h généralement)
3. ✅ **DCP validé** → Bouton "Télécharger" disponible
4. 💾 **Téléchargement** → Lien sécurisé 7 jours

---

## 📤 **Tutoriel Source (Producteur/Distributeur)**

### **Workflow Complet : De l'Invitation Email au DCP Validé**

#### **Étape 1 : Réception Invitation**
📧 **Email reçu** :
```
Objet: Invitation DCPrism - Upload requis pour "Amélie Poulain"
De: noreply@dcprism.com

Bonjour,

Le festival CANNES vous invite à uploader les DCPs suivants:
• CANNES_AMELIE_POULAIN_VF_2K_20250315  
• CANNES_AMELIE_POULAIN_VOST_2K_20250315

Lien d'accès: https://dcprism.com/panel/source
Identifiant: producteur@studiocanal.com
```

#### **Étape 2 : Première Connexion**
1. 🔗 Cliquez sur le lien dans l'email
2. 🔑 **Si premier accès** : Cliquez "Mot de passe oublié"
3. 📧 Email de définition mot de passe reçu
4. 🆕 Définissez votre mot de passe
5. 🔐 Connexion avec vos identifiants

#### **Étape 3 : Upload d'un DCP**
1. 📋 **Dashboard** affiche vos films assignés :
   ```
   Amélie Poulain - CANNES 2025
   ├── VF: 📤 À uploader
   ├── VOST: 📤 À uploader  
   └── Deadline: 10 Mars 2025
   ```

2. 🎯 **Sélectionner version** : Cliquez sur "VF: À uploader"

3. 📁 **Préparer votre DCP** :
   ```
   Formats acceptés:
   • Archive ZIP/TAR (recommandé si > 1GB)
   • Dossier complet (upload multiple fichiers)
   • Taille max: 4GB par fichier
   ```

4. ⬆️ **Upload** :
   - Glissez-déposez ou cliquez "Parcourir"
   - **Progression temps réel** affichée
   - Upload automatiquement repris si interruption

#### **Étape 4 : Suivi du Traitement**
1. ⏳ **Statuts successifs** :
   ```
   📤 Upload en cours... [████████░░] 80%
   ↓
   ⚙️ Traitement serveur... 
   ↓  
   🔍 Analyse technique...
   ↓
   ⏳ En attente validation...
   ```

2. 📧 **Notifications email** à chaque étape

#### **Étape 5 : Résultat Validation**

**✅ Cas 1 : DCP Validé**
```
🎉 Votre DCP a été validé !
├── Nomenclature: CANNES_AMELIE_POULAIN_VF_2K_20250315
├── Validé par: Technicien Jean Dupont  
├── Date: 08/03/2025 14:30
└── Prêt pour diffusion au festival
```

**❌ Cas 2 : DCP Rejeté**
```
⚠️ DCP rejeté - Correction nécessaire
├── Erreur principale: Checksums incorrects
├── Détails: Le fichier picture.mxf est corrompu
├── Actions: Re-générer le DCP et uploader à nouveau
└── Support: Contactez tech@dcprism.com si besoin
```

#### **Étape 6 : Re-Upload si Nécessaire**
1. 🔄 **Si rejeté** : Corrigez le problème identifié
2. 📤 **Nouveau upload** : Même processus, écrase la version précédente
3. 🔁 **Re-validation** : Nouveau cycle de validation

---

## 🔧 **Tutoriel Technicien Validation**

### **Workflow Complet : Validation Technique d'un DCP**

#### **Étape 1 : Accès Queue de Validation**
1. 🔐 Connexion `/panel/tech`  
2. 📋 **Dashboard Queue** affiche DCPs prioritaires :
   ```
   Queue Validation (12 DCPs)
   
   🔥 URGENT - Festival J-2
   ├── CANNES_TITANIC_VF_2K_20250313    
   
   📅 NORMAL  
   ├── BERLIN_AVATAR_VOST_4K_20250315
   ├── SUNDANCE_INDIE_FILM_VO_2K_20250320
   ```

#### **Étape 2 : Sélection et Analyse d'un DCP**
1. 🎯 **Cliquez sur un DCP** pour l'ouvrir
2. 🤖 **Analyse automatique déjà effectuée** :
   ```
   CANNES_TITANIC_VF_2K_20250313
   ├── 📁 Structure: ✅ Conforme DCI
   ├── 📋 Métadonnées: ✅ Complètes
   ├── 🔍 Checksums: ✅ Valides  
   ├── 🎬 Durée: 194 minutes
   ├── 📐 Résolution: 2048x1080 24fps
   └── 🔐 Encryption: Non chiffré
   ```

#### **Étape 3 : Validation Manuelle Détaillée**

**🔍 Vérifications Obligatoires :**

1. **Structure DCP** :
   ```
   ✅ ASSETMAP.xml présent
   ✅ PKL (Packing List) présent  
   ✅ CPL (Composition Playlist) présent
   ✅ Fichiers MXF (picture/sound) présents
   ✅ Certificats présents si chiffré
   ```

2. **Métadonnées Cohérentes** :
   ```
   ✅ Durée CPL = durée assets
   ✅ Résolution déclarée = réelle
   ✅ Frame rate cohérent
   ✅ Langues audio cohérentes avec version
   ```

3. **Intégrité Fichiers** :
   ```
   ✅ Checksums PKL = checksums calculés
   ✅ Taille fichiers cohérente  
   ✅ Pas de corruption détectée
   ```

#### **Étape 4 : Décision de Validation**

**✅ Cas 1 : Validation (DCP conforme)**
1. 👍 Cliquez **"Valider le DCP"**
2. 📝 **Commentaire optionnel** : "DCP techniquement conforme, prêt diffusion"
3. 💾 **Confirmer** → Notification automatique à tous les acteurs

**❌ Cas 2 : Rejet (DCP défaillant)**
1. 👎 Cliquez **"Rejeter le DCP"**  
2. 📂 **Sélectionner catégorie d'erreur** :
   ```
   • Erreur Structure (ASSETMAP, CPL, PKL)
   • Corruption Fichiers (Checksums incorrects)  
   • Métadonnées Incohérentes (Durée, résolution)
   • Format Non-Standard (Hors normes DCI)
   • Encryption Problems (KDM, certificats)
   ```

3. 📝 **Commentaires détaillés** (obligatoire) :
   ```
   Exemple:
   "Checksum incorrect pour picture.mxf. 
   Attendu: a1b2c3d4e5f6 
   Calculé: x9y8z7w6v5u4
   
   Action requise: Re-générer le DCP complet"
   ```

4. 📧 **Confirmation** → Email automatique vers source avec détails

#### **Étape 5 : Actions Groupées (Efficacité)**
1. ☑️ **Sélectionner plusieurs DCPs** (checkbox)
2. ⚡ **Actions groupées** disponibles :
   ```
   • Valider sélection (si aucun problème détecté)
   • Rejeter avec même motif (erreurs récurrentes)
   • Assigner priorité (urgences festival)
   • Exporter rapport (pour managers)
   ```

#### **Étape 6 : Suivi Performance**
📊 **Métriques personnelles** :
```
Technicien: Jean Dupont
├── DCPs validés aujourd'hui: 23
├── Taux validation: 87% (20 validés, 3 rejetés)
├── Temps moyen: 12 minutes/DCP
└── Spécialités: Films 4K, DCPs chiffrés
```

---

## 🎭 **Tutoriel Cinema (Exploitation)**

### **Workflow Complet : Recherche et Téléchargement de DCPs**

#### **Étape 1 : Recherche de Contenus**
1. 🔐 Connexion `/panel/cinema`
2. 🎪 **Sélectionner festival d'intérêt** :
   ```
   Festivals Disponibles:
   ├── 🎬 Festival de Cannes 2025 (45 DCPs)
   ├── 🐻 Berlinale 2025 (32 DCPs)  
   ├── 🎭 Sundance 2025 (67 DCPs)
   └── 🎨 Festival Local Montpellier (12 DCPs)
   ```

3. 🔍 **Filtres de recherche** :
   ```
   Genre: [Drame v] [Comédie] [Action] [Documentaire]
   Durée: [< 90min] [90-120min] [> 120min]  
   Langue: [VF] [VOST] [VO] [Autre]
   Format: [2K] [4K] [Tous]
   ```

#### **Étape 2 : Consultation Détaillée**
1. 🎬 **Cliquer sur un film** pour voir détails :
   ```
   "Le Fabuleux Destin d'Amélie Poulain"
   ├── 📋 Durée: 122 minutes
   ├── 🎭 Genre: Comédie romantique  
   ├── 📐 Format: 2K DCI (2048x1080) 24fps
   ├── 🎧 Langues: VF + VOST disponibles
   ├── 🔒 Encryption: Non chiffré
   ├── ✅ Validé le: 08/03/2025 par Tech Jean Dupont
   └── 📁 Taille: 127 GB
   ```

2. 🔧 **Vérification compatibilité** avec votre équipement :
   ```
   Serveur Projecteur: Barco Alchemy ICMP
   ├── ✅ 2K DCI supporté
   ├── ✅ 24fps supporté  
   ├── ✅ Non-chiffré OK
   └── ✅ Compatible 100%
   ```

#### **Étape 3 : Téléchargement Sécurisé**
1. 💾 **Cliquer "Télécharger"** 
2. 🔐 **Génération lien sécurisé** :
   ```
   Lien de téléchargement généré
   ├── 🔗 URL: https://secure.dcprism.com/download/abc123...
   ├── ⏰ Validité: 7 jours (expire le 15/03/2025)
   ├── 🔍 Checksums: Fournis pour vérification
   └── 📝 Notice technique: Incluse
   ```

3. ⬇️ **Téléchargement** :
   - Gestionnaire de téléchargement recommandé
   - Reprise possible si interruption  
   - Progression affichée en temps réel

#### **Étape 4 : Vérification Intégrité**
1. 🔍 **Checksums à vérifier** (obligatoire) :
   ```bash
   # Commandes exemple (Linux/Mac)
   md5sum CANNES_AMELIE_POULAIN_VF_2K_20250315.zip
   # Résultat attendu: a1b2c3d4e5f6789...
   ```

2. ✅ **Validation** : Checksum calculé = checksum fourni

#### **Étape 5 : Intégration Cinéma**
1. 📁 **Décompression** du DCP sur serveur
2. 🎬 **Import dans TMS** (Theatre Management System)
3. 🔧 **Test projection** recommandé avant première séance
4. 📅 **Association séances** dans planning

#### **Étape 6 : Support si Problème**
❌ **Si problème de projection** :
1. 📧 **Contact immédiat** : support@dcprism.com
2. 📝 **Informations à fournir** :
   ```
   • Nom du DCP téléchargé
   • Checksum vérifié (oui/non)
   • Équipement utilisé  
   • Message d'erreur exact
   • Screenshot si possible
   ```
3. ⚡ **Support prioritaire** pendant festivals actifs

---

## 🔄 **Tutoriel Workflows Avancés**

### **Gestion des DCPs Chiffrés (KDM)**

#### **Pour Techniciens** :
1. 🔐 **Détection automatique** : DCP chiffré signalé dans l'analyse
2. ✅ **Validation structure** : Même processus, certificats vérifiés
3. 📝 **Note spéciale** : "DCP chiffré - KDM requis pour projection"

#### **Pour Cinémas** :
1. 🎬 **Téléchargement** : DCP chiffré téléchargeable normalement  
2. 🔑 **KDM séparé** : Demande KDM au distributeur directement
3. 🔒 **Import** : KDM + DCP dans serveur pour déchiffrement

### **Upload de DCPs Volumineux (> 4GB)**

#### **Méthode Archive** :
```bash
# Compression recommandée
zip -r -9 mon_dcp.zip /chemin/vers/dcp/
# ou  
tar -czf mon_dcp.tar.gz /chemin/vers/dcp/
```

#### **Méthode Multipart** :
1. 📁 **Sélectionner dossier DCP** complet
2. ⬆️ **Upload automatique** : Système découpe en chunks
3. 🔄 **Reconstruction** : Auto-assemblage côté serveur

---

## 📞 **Support et Escalade**

### **Niveaux de Support** :
1. **Auto-assistance** : FAQ, tooltips, notifications contextuelles
2. **Email standard** : support@dcprism.com (réponse 24-48h)  
3. **Urgence festival** : Notification système directe (réponse 2h)

### **Bonnes Pratiques** :
- 📸 **Screenshots** pour problèmes visuels
- 📋 **Détails techniques** complets  
- ⏰ **Mentionner urgence** si festival imminent

---

*Tutoriels DCPrism - Workflows Détaillés*  
*Version 1.0 - 1er septembre 2025*
