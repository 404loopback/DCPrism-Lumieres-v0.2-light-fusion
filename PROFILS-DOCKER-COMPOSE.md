# Guide des Profils Docker Compose

## 🎯 Qu'est-ce que les profils ?

Les profils Docker Compose permettent d'organiser vos services en **groupes logiques** et de les démarrer sélectivement selon vos besoins.

## 💡 Problème résolu

**Avant les profils :**
```bash
# Vous deviez lancer TOUS les services à la fois
docker-compose up -d
# ↑ Lance 15+ containers même si vous en voulez que 3
```

**Avec les profils :**
```bash
# Vous pouvez choisir uniquement ce dont vous avez besoin
docker-compose --profile infrastructure up -d
# ↑ Lance seulement OpenTofu, Ansible et Infisical
```

## 📋 Comment ça marche dans DCPrism

### Dans notre `docker-compose.yml` :

```yaml
services:
  # Service SANS profil = démarre toujours
  mariadb:
    image: mariadb:11
    # Pas de "profiles:" = démarre avec "docker-compose up"
  
  # Services AVEC profils = démarrent seulement si demandé
  opentofu:
    profiles:
      - infrastructure  # Groupe "infrastructure"
      - tools          # Groupe "tools"
    build: ./lumiere/infra/docker/opentofu
  
  ansible:
    profiles:
      - infrastructure
      - tools
    build: ./lumiere/infra/docker/ansible
  
  infisical:
    profiles:
      - infrastructure
      - tools
      - security       # Aussi dans le groupe "security"
    build: ./lumiere/infra/docker/infisical
```

## 🚀 Utilisation pratique

### 1. Services de base uniquement
```bash
# Lance seulement fresnel, meniscus, mariadb, redis, etc.
# (tous les services SANS profil)
docker-compose up -d
```

### 2. Ajouter les outils d'infrastructure
```bash
# Lance les services de base + OpenTofu + Ansible + Infisical
docker-compose --profile tools up -d
```

### 3. Seulement les outils infrastructure
```bash
# Lance seulement OpenTofu + Ansible + Infisical (pas les apps)
docker-compose --profile infrastructure up -d
```

### 4. Seulement la gestion des secrets
```bash
# Lance seulement Infisical
docker-compose --profile security up -d
```

### 5. Combiner plusieurs profils
```bash
# Lance infrastructure + autres profils
docker-compose --profile infrastructure --profile security up -d
```

## 🎭 Nos profils définis

| Profil | Services inclus | Utilisation |
|--------|----------------|-------------|
| `infrastructure` | OpenTofu + Ansible + Infisical | Gestion infra complète |
| `tools` | OpenTofu + Ansible + Infisical | Tous les outils |
| `security` | Infisical | Gestion des secrets uniquement |
| *(aucun)* | fresnel, meniscus, mariadb, redis... | Applications principales |

## 🔧 Commandes utiles

### Voir tous les services (y compris avec profils)
```bash
docker-compose config --services
```

### Voir quels profils sont définis
```bash
docker-compose config --profiles
```

### Voir la configuration avec un profil
```bash
docker-compose --profile tools config
```

### Arrêter des services avec profils
```bash
docker-compose --profile tools down
```

## 💡 Avantages

1. **Performance** : Ne lance que ce dont vous avez besoin
2. **Ressources** : Économise CPU/RAM en dev
3. **Clarté** : Organise logiquement vos services
4. **Flexibilité** : Adapte l'environnement selon le contexte

## 📝 Exemple concret

```bash
# Développement web simple
docker-compose up -d
# → Lance seulement fresnel, meniscus, base de données

# Travail sur l'infrastructure
docker-compose --profile infrastructure up -d
# → Lance en plus OpenTofu, Ansible, Infisical

# Demo complète
docker-compose --profile tools up -d
# → Lance tout

# Nettoyage
docker-compose --profile tools down
# → Arrête tout ce qui a été lancé avec --profile tools
```

## 🎯 Pourquoi c'est utile pour DCPrism

- **Développeur web** : `docker-compose up` → Apps seulement
- **DevOps** : `--profile infrastructure` → Outils d'infra
- **Sécurité** : `--profile security` → Gestion des secrets
- **Demo complète** : `--profile tools` → Tout l'écosystème

Vous pouvez maintenant choisir exactement ce que vous voulez lancer selon votre travail du moment !
