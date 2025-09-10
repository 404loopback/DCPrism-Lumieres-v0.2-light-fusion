# 🎬 Workflows Métier DCPrism

**Documentation des processus métier et rôles utilisateurs**

---

## 👥 **Architecture des Rôles**

### **1. SuperAdmin** (Toi)
- **Dashboard** : En cours de développement
- **Responsabilités** : 
  - Gestion globale de la plateforme
  - Supervision de tous les festivals
  - Configuration système

### **2. Manager Festival**
- **Dashboard** : À développer
- **Responsabilités** :
  - Gestion du print traffic du festival
  - Création et gestion des utilisateurs Sources et Techniciens
  - Configuration de la nomenclature interne du festival

### **3. Utilisateur Source** (Distributeurs/Producteurs)
- **Dashboard** : À développer
- **Responsabilités** :
  - Upload des fichiers DCP
  - Sélection des versions demandées

### **4. Utilisateur Technicien**
- **Dashboard** : À développer
- **Responsabilités** :
  - Validation technique des DCP
  - Contrôle qualité

---

## 🔄 **Workflow Principal**

### **Phase 1 : Création Festival**

1. **SuperAdmin crée un festival**
2. **Assignation utilisateurs au festival**
   - Relation many-to-many via `user_festivals`
   - Peut assigner plusieurs Managers, Techniciens, etc.
   - Un utilisateur peut être assigné à plusieurs festivals

### **Phase 2 : Sélection Festival (Manager)**

3. **Manager se connecte et sélectionne un festival** :
   - Page d'accueil avec liste des festivals assignés
   - Choix du festival à administrer (contexte session)
   - Accès aux outils de gestion pour ce festival

### **Phase 3 : Création Films + Sources (Manager)**

4. **Manager crée un film** :
   - Renseigne titre, paramètres, versions attendues
   - **Saisit l'email de la source** → **Création automatique du compte Source** (si n'existe pas)
   - **Film automatiquement associé** au festival en cours d'administration
   - Relations : `Movie` ↔ `Festival` via `movie_festivals`

### **Phase 4 : Upload DCP (Source)**

5. **Utilisateur Source se connecte** :
   - Accède à son panel dédié
   - Visualise les versions demandées pour ses films
   - **Lance l'upload multipart (frontend only)** vers Backblaze
   - Un upload par version

### **Phase 5 : Analyse Automatique (Serveur externe)**

6. **Traitement automatique post-upload** :
   - Serveur externe analyse chaque fichier DCP uploadé
   - Génère un **rapport de conformité** dans chaque répertoire Backblaze
   - Complète les **DCP_parameters** automatiquement
   - Détermine le statut : **VALIDE** ou **NON CONFORME**

### **Phase 6 : Validation (À venir)**

7. **Intégration Cinémas (Futur)** :
   - Base de données des salles de cinéma
   - Mapping des paramètres DCP ↔ Paramètres salles
   - **Validation relationnelle** : Compatibilité DCP/Salle de diffusion

---

## 🎯 **Règles Métier Critiques**

### **Festivals & Utilisateurs**
- 🔄 **Relation many-to-many** : User ↔ Festival via `user_festivals`
- 👥 **Un festival** peut avoir plusieurs Managers/Techniciens
- 🎪 **Un utilisateur** peut gérer plusieurs festivals
- 🎯 **Contexte festival** : Manager sélectionne le festival à administrer

### **Films & Versions**
- 🎬 **Un film** = Plusieurs versions possibles
- 📝 **Nomenclature générée automatiquement** selon paramètres
- 🎨 **Nomenclature personnalisable** par festival (ordre + champs custom)

### **Comptes Utilisateurs**
- 📧 **Email source** → Création automatique compte Source (si inexistant)
- 🎬 **Film auto-associé** au festival en cours d'administration
- 🔐 **Un compte Source** peut avoir plusieurs films
- 👤 **SuperAdmin assigne** les utilisateurs aux festivals

### **Upload & Validation**
- ⬆️ **Upload multipart frontend-only** vers Backblaze
- 🔄 **Analyse DCP externe** (autre projet - hors scope)
- 👨‍💻 **Validation manuelle** par Technicien assigné au festival
- ✅/❌ **Statut DCP** : uploaded → processing → valid/invalid

---

## 🗂️ **Modèles de Données Impliqués**

### **Principaux**
- `User` ↔ `Festival` (many-to-many via `user_festivals`)
- `Movie` ↔ `Festival` (many-to-many via `movie_festivals`)
- `Movie` → `Version` (un à plusieurs)
- `Version` → `Dcp` (un à plusieurs)
- `Movie.source_email` → Création auto `User` (Source)

### **Paramètres & Configuration**
- `Parameter` (paramètres techniques DCP)
- `DcpParameter` (paramètres extraits du DCP)
- `NomenclatureConfig` (configuration par festival)

### **Futurs (Intégration Cinémas)**
- `Cinema` (salles de cinéma)
- `CinemaParameter` (paramètres techniques salles)
- `CompatibilityRule` (règles de compatibilité DCP/Salle)

---

## 🚀 **État d'Avancement**

### ✅ **Terminé**
- Infrastructure Laravel 12 + Filament 4
- Modèles de base (Festival, Movie, Version, Dcp, Lang)
- Dashboard SuperAdmin (ressources DCP management)

### 🔄 **En Cours (Phase 2)**
- Dashboard Manager Festival
- Workflow création Films → Versions
- Système nomenclature personnalisable
- Création automatique comptes Sources

### 📅 **À Venir (Phase 3)**
- Interface Source (sélection versions + upload)
- Upload multipart Backblaze frontend
- Intégration serveur analyse externe
- Dashboard Technicien

### 🔮 **Futur (Phase 4)**
- Base données Cinémas
- Validation relationnelle DCP/Salle
- Système de compatibilité avancé

---

## 💡 **Points Techniques Clés**

### **Upload Backblaze**
- **Multipart upload** géré côté frontend uniquement
- **Un répertoire par version** sur Backblaze
- **Rapport conformité** déposé automatiquement par serveur externe

### **Nomenclature Dynamique**
- **Configurée par festival** (ordre + champs custom)
- **Générée automatiquement** à partir des paramètres Movie
- **Appliquée aux versions** pour organisation des fichiers

### **Validation DCP**
- **Analyse automatique** post-upload (serveur externe)
- **DCP_parameters** complétés automatiquement
- **Statut VALIDE/NON** basé sur règles métier festival

---

*Documentation créée le 31 août 2025*  
*Workflow documenté par SuperAdmin pour Phase 2 de développement*
