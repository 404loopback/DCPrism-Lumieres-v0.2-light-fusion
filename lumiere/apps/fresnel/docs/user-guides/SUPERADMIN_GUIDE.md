# 👨‍💻 Guide Utilisateur SuperAdmin DCPrism

**Rôle :** SuperAdmin - Gestion globale du système  
**Panel d'accès :** `/panel/admin`  
**Niveau d'autorisation :** Maximum - Contrôle total

---

## 🎯 **Présentation du Rôle**

En tant que **SuperAdmin**, vous disposez d'un accès complet à l'ensemble de la plateforme DCPrism. Vous êtes responsable de :

- 🏢 **Gestion des festivals** et assignation des Manager
- 👥 **Administration des utilisateurs** et gestion des rôles  
- ⚙️ **Configuration système** (nomenclatures, paramètres)
- 📊 **Monitoring global** et supervision de l'activité
- 🔧 **Maintenance** et optimisation de la plateforme

---

## 🚀 **Accès au Dashboard**

### Connexion
1. Accédez à : `https://votre-domaine.com/panel/admin`
2. Connectez-vous avec vos identifiants SuperAdmin
3. Le dashboard vous présente une vue d'ensemble complète

### Dashboard Principal
Le dashboard affiche :
- **Statistiques globales** : Films, DCPs, utilisateurs actifs
- **Activité récente** : Dernières actions système
- **Métriques de performance** : Usage stockage, queues, jobs
- **Alertes système** : Problèmes nécessitant attention

---

## 🏢 **Gestion des Festivals**

### Créer un Festival
1. **Navigation** : `Festivals` → `Nouveau Festival`
2. **Informations requises** :
   - Nom du festival
   - Subdomain (URL unique)
   - Email de contact
   - Dates du festival
   - Description

3. **Configuration avancée** :
   - Paramètres de nomenclature
   - Règles de validation
   - Quotas de stockage

### Assigner un Manager
1. **Sélectionner le festival** dans la liste
2. **Onglet "Managers"** → `Assigner Manager`
3. **Choisir l'utilisateur** ou créer un nouveau compte
4. **Définir les permissions** spécifiques si nécessaire

### Surveillance Festival
- **Statistiques** : Nombre de films, DCPs, activité
- **Stockage utilisé** : Quota B2, projections futures
- **Performance** : Temps de traitement, taux de validation

---

## 👥 **Administration Utilisateurs**

### Types d'Utilisateurs
- **Manager** : Gestion d'un festival spécifique
- **Source** : Upload de DCPs pour leurs films
- **Tech** : Validation technique des DCPs
- **Cinema** : Téléchargement des DCPs validés

### Créer un Utilisateur
1. **Navigation** : `Utilisateurs` → `Nouvel Utilisateur`
2. **Informations** :
   - Email (identifiant unique)
   - Nom complet
   - Rôle
   - Festival associé (pour Manager)

3. **Configuration** :
   - Mot de passe temporaire
   - Permissions spécifiques
   - Notifications activées

### Gestion des Accès
- **Logs de connexion** : Historique par utilisateur
- **Sessions actives** : Surveillance temps réel
- **Permissions** : Modification des droits d'accès
- **Désactivation** : Suspension temporaire ou définitive

---

## ⚙️ **Configuration Système**

### Nomenclatures
**Fonction** : Règles de nommage automatique des DCPs

1. **Accès** : `Configuration` → `Nomenclatures`
2. **Création** :
   - Paramètres utilisés (titre, année, format, etc.)
   - Ordre d'assemblage
   - Séparateurs et préfixes
   - Règles conditionnelles

3. **Test** : Prévisualisation avec exemples réels

### Paramètres Globaux
- **Taille max upload** : Limite par fichier DCP
- **Timeout analyse** : Durée max traitement
- **Rétention logs** : Durée conservation données
- **Notifications** : Configuration emails système

### Intégrations Externes
- **Backblaze B2** : Configuration stockage cloud
- **Services analyse** : API externes validation DCP
- **Monitoring** : Sentry, métriques, alertes

---

## 📊 **Monitoring et Supervision**

### Dashboard Métriques
**Widgets disponibles** :
- **Activité globale** : Films traités, DCPs validés
- **Performance système** : CPU, RAM, stockage
- **Queues de traitement** : Jobs en cours, temps d'attente
- **Erreurs** : Taux d'échec, alertes critiques

### Logs et Audit Trail
1. **Logs d'activité** : Toutes actions utilisateurs
2. **Logs système** : Erreurs, performances, sécurité
3. **Audit GDPR** : Traçabilité données personnelles
4. **Export** : Données pour analyse externe

### Gestion des Jobs
- **Queues actives** : `default`, `dcp_analysis`, `dcp_validation`
- **Jobs échoués** : Diagnostic et relance
- **Performance** : Temps moyen, débit
- **Horizon** : Interface monitoring avancé

---

## 🔧 **Maintenance Système**

### Tâches Récurrentes
**Quotidiennes** :
- Vérification espace disque
- Contrôle performance queues
- Validation sauvegardes

**Hebdomadaires** :
- Nettoyage logs anciens
- Optimisation base données
- Mise à jour métriques

**Mensuelles** :
- Audit sécurité complet
- Revue utilisation ressources
- Planification maintenance

### Commandes Maintenance
```bash
# Nettoyage système
php artisan dcprism:cleanup

# Optimisation performances
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# Monitoring queues
php artisan horizon:status
php artisan queue:monitor

# Sauvegarde
php artisan backup:run
```

### Résolution Problèmes
**Queue bloquée** :
1. Vérifier Horizon : `horizon:terminate` puis `horizon`
2. Contrôler Redis : connexion et mémoire
3. Redémarrer workers si nécessaire

**Upload B2 échoué** :
1. Vérifier credentials Backblaze
2. Contrôler quotas bucket
3. Tester connectivité API

**Performance dégradée** :
1. Analyser logs Laravel
2. Vérifier usage RAM/CPU serveur
3. Optimiser requêtes DB lentes

---

## 📋 **Workflows Administratifs**

### Onboarding Festival
1. **Créer festival** avec configuration de base
2. **Configurer nomenclature** selon besoins
3. **Créer compte Manager** et assigner
4. **Paramétrer quotas** stockage et utilisateur
5. **Tester workflow** avec données exemple

### Incident Majeur
1. **Identifier** la source via monitoring
2. **Isoler** le problème (queue, service, etc.)
3. **Corriger** selon procédures
4. **Documenter** dans logs audit
5. **Communiquer** aux utilisateurs impactés

### Migration Données
1. **Sauvegarder** état actuel
2. **Préparer** environnement cible
3. **Migrer** par étapes (utilisateurs, festivals, DCPs)
4. **Valider** intégrité données
5. **Basculer** trafic production

---

## ⚠️ **Alertes et Notifications**

### Types d'Alertes
- 🔴 **Critique** : Système indisponible, corruption données
- 🟡 **Attention** : Performance dégradée, quota atteint
- 🔵 **Information** : Maintenance programmée, nouveautés

### Canaux Notification
- **Dashboard** : Notifications temps réel
- **Email** : Alertes critiques et rapports quotidiens
- **Slack** : (optionnel) Intégration équipe technique

### Configuration Alertes
1. **Seuils** : CPU > 80%, RAM > 85%, Queue > 1000 jobs
2. **Fréquence** : Immediate (critique), 15min (warning), 1h (info)
3. **Destinataires** : SuperAdmin + équipe technique

---

## 🔒 **Sécurité et Conformité**

### Bonnes Pratiques
- **Mots de passe** : Complexes, renouvelés régulièrement
- **2FA** : Activé pour tous les SuperAdmin
- **Sessions** : Timeout automatique, déconnexion sécurisée
- **Audit** : Logs de toutes actions administratives

### Conformité GDPR
- **Données personnelles** : Minimisation, pseudonymisation
- **Retention** : Suppression automatique après délai
- **Export** : Possibilité extraction données utilisateur
- **Anonymisation** : Effacement données sensibles

### Backup et Récupération
- **Sauvegarde quotidienne** : Base + fichiers critiques
- **Rétention** : 30 jours local + 12 mois externe
- **Test restauration** : Mensuel sur environnement test
- **RTO/RPO** : < 4h restauration, < 24h perte max

---

## 📞 **Support et Ressources**

### Documentation Technique
- **Architecture** : `/docs/refactored-architecture.md`
- **API** : `/docs/api-guide.md`
- **Déploiement** : `/docs/docker-setup.md`

### Contacts Escalade
- **Développement** : dev@dcprism.com
- **Infrastructure** : ops@dcprism.com
- **Sécurité** : security@dcprism.com

### Formation Continue
- Formation mensuelle nouvelles fonctionnalités
- Webinars techniques trimestriels
- Documentation mise à jour en continu

---

*Guide SuperAdmin DCPrism - Version 1.0*  
*Dernière mise à jour : 31 août 2025*
