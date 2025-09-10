# Architecture Docker Moderne DCPrism-Laravel

## 🏗️ Architecture Actuelle

**Basée sur Traefik + PHP-FPM + Services Séparés**

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│     Internet    │────│     Traefik      │────│   Laravel App   │
│                 │    │  (Reverse Proxy) │    │   (PHP-FPM)     │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                              │                        │
                              │                        ├── Worker (Queue)
                              │                        ├── Scheduler (Cron)
                              │                        │
                              └────┬────────────┬──────┴──────┬─────────┬────────┐
                                   │            │             │         │        │
                                ┌──▼──┐    ┌────▼───┐    ┌───▼───┐ ┌───▼──┐ ┌───▼────┐
                                │     │    │        │    │       │ │      │ │        │
                                │ DB  │    │ Redis  │    │Adminer│ │ Mail │ │ Redis  │
                                │     │    │        │    │       │ │ Hog  │ │ Cmd    │
                                └─────┘    └────────┘    └───────┘ └──────┘ └────────┘
```

## 🗂️ Structure des Fichiers

```
DCPrism-Laravel/
├── docker/
│   ├── app/
│   │   ├── Dockerfile          # Multi-stage Laravel (dev/prod)
│   │   ├── php.ini             # Config PHP optimisée  
│   │   ├── php-fpm.conf        # Config PHP-FPM
│   │   └── start.sh            # Script de démarrage
│   ├── traefik/
│   │   ├── traefik.yml         # Config principale Traefik
│   │   └── dynamic/
│   │       └── assets.yml      # Config assets statiques
│   ├── worker/
│   │   └── start.sh            # Script queue worker
│   ├── scheduler/
│   │   └── start.sh            # Script scheduler
│   └── adminer/
│       └── Dockerfile          # Interface DB
├── docker-compose.modern.yml   # Architecture moderne
├── scripts/
│   ├── migrate-to-modern.sh    # Migration automatique
│   └── docker-maintenance.sh   # Outils de maintenance
├── archive/
│   └── legacy-docker-*/        # Anciennes architectures
└── DOCKER-ARCHITECTURE.md     # Cette documentation
```

## 🚀 Services Disponibles

| Service | URL | Port | Description |
|---------|-----|------|-------------|
| **Application** | https://dcprism.local | 443 | Application Laravel principale |
| **Traefik Dashboard** | http://localhost:8080 | 8080 | Interface d'administration Traefik |
| **Adminer** | https://adminer.dcprism.local | 443 | Interface base de données |
| **MailHog** | https://mail.dcprism.local | 443 | Test des emails |
| **Redis Commander** | https://redis.dcprism.local | 443 | Interface Redis |

## ✅ Avantages de cette Architecture

### 1. **Efficacité**
- ✅ Une seule image Laravel partagée (économie de ~4GB)
- ✅ Build plus rapide avec cache Docker
- ✅ Multi-stage builds optimisés

### 2. **Sécurité**
- ✅ SSL automatique avec Let's Encrypt
- ✅ Headers de sécurité automatiques
- ✅ Isolation des services

### 3. **DevOps**
- ✅ Service discovery automatique
- ✅ Load balancing intelligent
- ✅ Health checks intégrés
- ✅ Logs centralisés

### 4. **Maintenance**
- ✅ Configuration via labels Docker
- ✅ Hot reload sans redémarrage
- ✅ Monitoring via dashboard
- ✅ Scripts d'automatisation

## 🛠️ Commandes Utiles

### Démarrage
```bash
# Démarrer tous les services
docker-compose up -d

# Voir le statut des services  
docker-compose ps

# Voir les logs
docker-compose logs -f
```

### Maintenance
```bash
# Script de maintenance complet
./scripts/docker-maintenance.sh status

# Health check de tous les services
./scripts/docker-maintenance.sh health

# Monitoring en temps réel
./scripts/docker-maintenance.sh monitor

# Scaling des workers
./scripts/docker-maintenance.sh scale 3
```

### Développement
```bash
# Rebuild après modifications
docker-compose build app
docker-compose up -d

# Accès au container Laravel
docker-compose exec app bash

# Exécuter des commandes Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan queue:work
```

## 🔄 Migration et Rollback

### Migration vers cette architecture
```bash
./scripts/migrate-to-modern.sh
```

### Rollback vers l'ancienne architecture  
```bash
cd archive/legacy-docker-YYYYMMDD_HHMMSS/
./restore.sh
```

## 🏷️ Tags et Versions

- **Image principale**: `dcprism-laravel:latest`
- **Architecture**: `Traefik v3.0 + PHP 8.3 + Laravel`
- **Version Docker Compose**: `3.8+`

## 🔧 Configuration

### Variables d'environnement importantes
- `USER_ID` / `GROUP_ID`: Permissions fichiers
- `TRAEFIK_LOG_LEVEL`: Niveau de logs Traefik
- Voir `.env.docker` pour Laravel

### Domaines locaux
```
dcprism.local                 # Application principale
traefik.dcprism.local         # Dashboard Traefik  
adminer.dcprism.local         # Interface DB
mail.dcprism.local            # MailHog
redis.dcprism.local           # Redis Commander
```

## 📊 Monitoring

### Health Checks
Tous les services ont des health checks configurés :
- Laravel: `/fpm-ping`
- MariaDB: `mysqladmin ping`
- Redis: `redis-cli ping`
- Traefik: `/ping`

### Métriques
- Dashboard Traefik : http://localhost:8080
- Métriques Prometheus disponibles
- Logs JSON structurés

## 🛡️ Sécurité

### TLS/SSL
- Certificats Let's Encrypt automatiques
- Redirection HTTP → HTTPS
- Headers de sécurité configurés

### Réseau
- Réseau Docker isolé `dcprism-network`  
- Services exposés uniquement via Traefik
- Pas d'accès direct aux services internes

---

*Architecture mise à jour le $(date)*
*Pour support: voir les scripts dans ./scripts/*
