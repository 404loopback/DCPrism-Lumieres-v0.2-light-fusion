# 📚 Documentation DCPrism Laravel

Documentation complète du projet DCPrism, plateforme de gestion des Digital Cinema Packages (DCP) pour festivals de cinéma.

---

## 📋 **Structure de la Documentation**

### 🔄 **Migration & Architecture**
- **[Migration Overview](./migration/README.md)** - Index de la migration Lumen→Laravel
- **[Migration Report](./migration/MIGRATION_REPORT.md)** - Rapport technique complet (85% complété)
- **[Business Workflows](./migration/WORKFLOWS_METIER.md)** - Processus métier et rôles utilisateur

### 🛠️ **Installation & Configuration**  
- **[Docker Setup](./docker-setup.md)** - Configuration environnement de développement
- **[Installation Success](./installation-success.md)** - Guide installation Laravel + Filament
- **[Coexistence Guide](./coexistence-guide.md)** - Cohabitation ancien/nouveau système
- **[Ports Summary](./ports-summary.md)** - Résumé des ports utilisés

### 🏗️ **Architecture & Développement**
- **[Refactored Architecture](./refactored-architecture.md)** - Architecture Laravel 12 + Filament 4
- **[API Guide](./api-guide.md)** - Documentation API endpoints
- **[Testing Guide](./testing-guide.md)** - Guide des tests automatisés

---

## 🎯 **État du Projet**

### ✅ **Migration 85% Complétée**
- ✅ **Architecture & Infrastructure** : Laravel 12 + Filament 4 opérationnel
- ✅ **Services Critiques** : Upload B2, Nomenclature, Analyse DCP migrés
- ✅ **Interface Multi-Panels** : 6 panels fonctionnels (Admin, Manager, Source, Tech, Cinema, Supervisor)
- ✅ **Sécurité** : Policies et Gates implémentées
- ✅ **Observabilité** : Monitoring et audit GDPR intégrés

### 🟡 **Travail Restant (15%)**
- Tests end-to-end complets
- Configuration production (Redis, Horizon)
- Documentation utilisateur finale

---

## 🎬 **À Propos de DCPrism**

DCPrism est un **système de Print Traffic multi-rôles** pour festivals de cinéma, permettant :

- 🎪 **Configuration par festival** avec nomenclature personnalisable
- 📤 **Upload multipart vers Backblaze B2** avec progression temps réel  
- 🤖 **Analyse automatisée des DCPs** avec rapport de conformité
- ✅ **Workflow de validation technique** par rôles spécialisés

### **Rôles Utilisateurs**
- 👨‍💻 **SuperAdmin** : Gestion globale festivals + assignation Managers
- 🎪 **Manager Festival** : Création films/versions + gestion Sources
- 📤 **Source** : Upload DCPs multipart avec sélection versions
- 🔧 **Technicien** : Validation manuelle + contrôle qualité
- 🎭 **Cinema** : Téléchargement DCPs validés (futur)
- 👀 **Supervisor** : Supervision et monitoring global

---

## 🚀 **Démarrage Rapide**

```bash
# Clone et démarrage Docker
git clone [repo-url] dcprism-laravel
cd dcprism-laravel
docker-compose up -d

# URLs principales
# - Application: http://localhost:8001
# - Admin Panel: http://localhost:8001/panel/admin
# - BDD Admin: http://localhost:8082
```

**Identifiants par défaut:**
- Admin : `admin@dcprism.local` / `admin123`

---

## 📞 **Support & Contacts**

- **Architecture** : Documentation dans cette section `/docs/`
- **Code** : Modèles dans `/app/Models/`, Services dans `/app/Services/`
- **Interface** : Ressources Filament dans `/app/Filament/`
- **Tests** : Suite de tests dans `/tests/`

---

*Documentation maintenue par l'équipe DCPrism Laravel*  
*Dernière mise à jour : 31 août 2025*
