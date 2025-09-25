# Fonctionnalité Provider/Partenaire - Utilisateurs DCPrism

## Vue d'ensemble

Cette fonctionnalité permet de marquer certains utilisateurs comme **Provider/Partenaire** dans le système DCPrism. Les comptes Provider bénéficient de privilèges spéciaux, notamment en ce qui concerne la désactivation automatique.

## Principe de fonctionnement

### Qu'est-ce qu'un Provider/Partenaire ?

Un Provider/Partenaire est un utilisateur privilégié qui :
- **Ne peut jamais être désactivé automatiquement** par le système
- **Conserve son accès** même sans festival assigné
- Représente généralement des **partenaires commerciaux** ou des **fournisseurs de contenu permanents**

### Base de données

La fonctionnalité utilise le champ existant `is_partner` dans la table `users` :

```sql
is_partner BOOLEAN DEFAULT FALSE COMMENT 'Partenaire permanent - compte non désactivé sans festival'
```

## Interface Administrateur

### 1. Formulaire d'édition utilisateur

**Localisation** : `http://localhost/fresnel/admin/users/{id}/edit`

**Nouveau champ ajouté** :
- **Toggle "Compte Provider/Partenaire"**
- **Description** : "Les comptes partenaires ne sont jamais désactivés automatiquement, même sans festival assigné"
- **Position** : Section "Configuration avancée"

```php
Toggle::make('is_partner')
    ->label('Compte Provider/Partenaire')
    ->helperText('Les comptes partenaires ne sont jamais désactivés automatiquement, même sans festival assigné')
    ->default(false)
    ->inline(false)
```

### 2. Liste des utilisateurs

**Nouvelles fonctionnalités** :

#### Colonne Provider
- **Icône** : ⭐ (étoile) pour les providers, 👤 (utilisateur) pour les comptes standard
- **Couleur** : Orange pour les providers, gris pour les standard
- **Tooltip** : Information contextuelle au survol
- **Triable** : Oui

#### Filtre Type de compte
- **Tous** : Affiche tous les utilisateurs
- **Providers/Partenaires** : Affiche uniquement les comptes partenaires
- **Comptes standard** : Affiche uniquement les comptes non-partenaires

#### Vue détaillée (Modal)
- **Champ "Type de compte"** avec badge coloré
- **Icône** correspondante (étoile/utilisateur)

## Logique de protection

### Méthodes dans le modèle User

```php
/**
 * Vérifier si l'utilisateur est un partenaire
 */
public function isPartner(): bool
{
    return $this->is_partner === true;
}

/**
 * Vérifier si l'utilisateur doit être protégé de la désactivation automatique
 */
public function isProtectedFromDeactivation(): bool
{
    return $this->isPartner() || $this->hasRole(['super_admin', 'admin']);
}
```

### Protection dans l'interface

1. **Action "Désactiver"** : Masquée pour les utilisateurs protégés
2. **Désactivation en masse** : Les providers sont automatiquement exclus
3. **Message d'explication** : Information claire sur la protection

## Cas d'usage

### 1. **Distributeur de contenu**
Un distributeur qui fournit régulièrement des films à plusieurs festivals :
- Marqué comme Provider ✅
- Accès permanent même entre les festivals
- Ne risque pas de désactivation accidentelle

### 2. **Partenaire technologique**
Un prestataire technique qui gère l'infrastructure DCP :
- Marqué comme Provider ✅
- Accès maintenu pour le support technique
- Compte toujours actif

### 3. **Créateur de contenu indépendant**
Un réalisateur qui participe ponctuellement :
- **Pas** marqué comme Provider ❌
- Désactivation possible si inactif
- Réactivation lors de nouveaux festivals

## Interface utilisateur

### Indicateurs visuels

| Élément | Provider | Standard |
|---------|----------|----------|
| **Icône** | ⭐ (heroicon-o-star) | 👤 (heroicon-o-user) |
| **Couleur** | Orange (warning) | Gris (gray) |
| **Badge** | "Provider/Partenaire" | "Standard" |

### Actions disponibles

| Action | Provider | Standard | Admin/Super Admin |
|--------|----------|----------|-------------------|
| **Désactiver** | ❌ Interdite | ✅ Autorisée | ❌ Interdite |
| **Modifier** | ✅ Autorisée | ✅ Autorisée | ✅ Autorisée |
| **Supprimer** | ⚠️ Avec confirmation | ✅ Standard | ❌ Interdite |

## Tests de validation

### Scénarios à valider

#### ✅ Interface
- [ ] Le toggle Provider est visible dans le formulaire d'édition
- [ ] La colonne Provider s'affiche correctement dans la liste
- [ ] Le filtre "Type de compte" fonctionne
- [ ] La vue détaillée affiche le statut Provider

#### ✅ Protection contre la désactivation
- [ ] Un Provider ne peut pas être désactivé via l'action individuelle
- [ ] Les Providers sont exclus de la désactivation en masse
- [ ] Le message d'erreur s'affiche si tentative de désactivation

#### ✅ Comportement métier
- [ ] Un Provider reste actif même sans festival
- [ ] La méthode `isProtectedFromDeactivation()` fonctionne
- [ ] Les droits admin + provider sont cumulés

## Commandes de test

```bash
# Tester la méthode isPartner()
php artisan tinker

# Dans tinker :
$user = App\Models\User::find(1);
$user->update(['is_partner' => true]);
$user->isPartner(); // true
$user->isProtectedFromDeactivation(); // true

# Tester la logique de protection
$user->update(['is_partner' => false]);
$user->isProtectedFromDeactivation(); // false (sauf si admin)
```

## Migration (si nécessaire)

La colonne `is_partner` existe déjà dans la migration de base :

```php
// 0001_01_01_000000_create_users_table.php
$table->boolean('is_partner')->default(false)->comment('Partenaire permanent - compte non désactivé sans festival');
```

**Aucune migration supplémentaire requise** ✅

## Évolutions futures

### Fonctionnalités envisagées

1. **Niveaux de partenariat** : Bronze, Silver, Gold
2. **Expiration de statut** : Date limite du statut Provider
3. **Privilèges étendus** : Accès prioritaire, limites augmentées
4. **Notification automatique** : Alertes avant expiration
5. **Statistiques** : Tableau de bord des partenaires

### API Extensions

1. **Endpoint `/api/providers`** : Liste des providers actifs
2. **Webhook de statut** : Notification changement de statut
3. **Intégration CRM** : Synchronisation avec système externe

---

**Date de création** : 23/09/2024  
**Dernière mise à jour** : 23/09/2024  
**Version** : 1.0  
**Fichiers modifiés** :
- `Modules/Fresnel/app/Filament/Resources/Users/Schemas/UserForm.php`
- `Modules/Fresnel/app/Filament/Resources/Users/Tables/UserTable.php`
