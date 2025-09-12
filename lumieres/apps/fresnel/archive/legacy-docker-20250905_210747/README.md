# Archive des Anciennes Architectures Docker DCPrism

Cette archive contient les anciennes configurations Docker avant la migration vers l'architecture moderne avec Traefik.

## Contenu

- `Dockerfile.original` : Dockerfile original avec Supervisor
- `Dockerfile.optimized` : Version optimisée avec multi-stages  
- `docker-compose.yml.current` : Configuration Docker Compose avant migration
- `docker-compose.optimized.yml` : Version avec image partagée
- `docker-compose.prod.yml` : Configuration de production
- `php-configs/` : Configurations PHP et PHP-FPM
- `supervisor-configs/` : Configurations Supervisor
- `mysql-configs/` : Configurations MariaDB
- `nginx-configs/` : Configurations Nginx (archivées)

## Architecture Précédente

L'ancienne architecture utilisait :
- 3 images Laravel séparées (app, worker, scheduler) = 6GB+
- Nginx comme serveur web
- Supervisor pour gérer les processus
- Configuration manuelle des certificats SSL
- Pas de service discovery

## Pour Restaurer

Si vous souhaitez revenir à l'ancienne architecture :

```bash
# Arrêter la nouvelle architecture
docker-compose down

# Restaurer les fichiers
cp archive/legacy-docker-YYYYMMDD_HHMMSS/docker-compose.yml.current docker-compose.yml
cp archive/legacy-docker-YYYYMMDD_HHMMSS/Dockerfile.original Dockerfile

# Redémarrer
docker-compose up -d --build
```

## Migration Réalisée

Date de migration : $(date)
Architecture cible : Traefik + PHP-FPM + Services séparés
Gain d'espace : ~4GB (images dupliquées supprimées)
Nouveaux services : SSL automatique, service discovery, dashboard Traefik
