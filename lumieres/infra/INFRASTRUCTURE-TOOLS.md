# Infrastructure Tools

Ce document décrit les outils d'infrastructure disponibles dans DCPrism pour la gestion de l'infrastructure, des secrets et de l'automatisation.

## Services Disponibles

### OpenTofu
**Status**: ✅ ACTIF  
**Container**: `dcprism-opentofu`  
**Version**: 1.6.2  
**Usage**: `docker-compose exec opentofu tofu --help`

#### Utilisation
```bash
# Lancer le service OpenTofu
docker-compose --profile infrastructure up opentofu

# Exécuter des commandes OpenTofu
docker-compose exec opentofu tofu --version
docker-compose exec opentofu tofu init
docker-compose exec opentofu tofu plan
docker-compose exec opentofu tofu apply
```

#### Dossiers
- `lumiere/infra/terraform/`: Fichiers de configuration Terraform/OpenTofu
- Cache des plugins: Volume Docker `opentofu-cache`
- Données Terraform: Volume Docker `opentofu-data`

### Ansible
**Status**: ✅ ACTIF  
**Container**: `dcprism-ansible`  
**Version**: 2.19.2  
**Usage**: `docker-compose exec ansible ansible --help`

#### Utilisation
```bash
# Lancer le service Ansible
docker-compose --profile infrastructure up ansible

# Exécuter des commandes Ansible
docker-compose exec ansible ansible --version
docker-compose exec ansible ansible-playbook playbooks/example.yml
docker-compose exec ansible ansible all -m ping
```

#### Dossiers
- `lumiere/infra/ansible/`: Playbooks, rôles et inventaires Ansible
- `lumiere/infra/ansible/playbooks/`: Playbooks Ansible
- `lumiere/infra/ansible/inventory/`: Inventaires
- `lumiere/infra/ansible/roles/`: Rôles Ansible
- Logs: Volume Docker `ansible-logs`

### Infisical
**Status**: ✅ ACTIF  
**Container**: `dcprism-infisical`  
**Port**: 3000  
**Interface**: http://localhost:3000  
**CLI**: `docker-compose exec infisical infisical --help`

#### Utilisation
```bash
# Lancer le service Infisical
docker-compose --profile security up infisical

# Accéder à l'interface web
# Ouvrir http://localhost:3000 dans le navigateur

# Utiliser le CLI Infisical
docker-compose exec infisical infisical --version
docker-compose exec infisical infisical login
docker-compose exec infisical infisical secrets list
```

#### Dossiers
- `lumiere/infra/secrets/`: Données et configurations de secrets
- Logs: Volume Docker `infisical-logs`
- Configuration: Volume Docker `infisical-config`

## Profils Docker Compose

Les services d'infrastructure utilisent des profils Docker Compose pour une gestion granulaire :

### Profil `infrastructure`
Lance OpenTofu et Ansible pour la gestion d'infrastructure :
```bash
docker-compose --profile infrastructure up -d
```

### Profil `tools` 
Lance tous les outils (OpenTofu, Ansible, Infisical) :
```bash
docker-compose --profile tools up -d
```

### Profil `security`
Lance uniquement Infisical pour la gestion des secrets :
```bash
docker-compose --profile security up -d
```

## Configuration et Personnalisation

### Variables d'Environnement
Chaque service peut être configuré via des variables d'environnement dans le fichier `.env` ou directement dans `docker-compose.yml`.

### Volumes et Persistance
- Les configurations sont montées en lecture seule depuis `lumiere/infra/docker/`
- Les données de travail sont stockées dans des volumes Docker nommés
- Les logs sont centralisés dans des volumes dédiés

### Sécurité
- Tous les services utilisent des utilisateurs non-root
- Les clés SSH sont montées en lecture seule depuis `~/.ssh`
- Infisical gère le chiffrement des secrets de manière sécurisée

## Intégration avec les Applications

Ces outils sont conçus pour s'intégrer avec les applications Fresnel et Meniscus :

1. **OpenTofu** : Provisioning de l'infrastructure cloud
2. **Ansible** : Configuration et déploiement des applications
3. **Infisical** : Injection sécurisée des secrets dans les conteneurs

## Démarrage Rapide

```bash
# Lancer tous les outils d'infrastructure
docker-compose --profile tools up -d

# Vérifier le statut
docker-compose ps

# Accéder aux services
docker-compose exec opentofu tofu version
docker-compose exec ansible ansible --version
docker-compose exec infisical infisical version

# Interface web Infisical
open http://localhost:3000
```

## Développement et Debug

Pour le développement et le debugging :

```bash
# Voir les logs en temps réel
docker-compose --profile tools logs -f

# Accéder à un shell dans un container
docker-compose exec opentofu bash
docker-compose exec ansible bash
docker-compose exec infisical sh

# Reconstruire les images
docker-compose --profile tools build --no-cache
```
