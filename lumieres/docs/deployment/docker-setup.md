# DCPrism-Laravel - Configuration Docker

## 🎯 Vue d'ensemble

Configuration Docker complète pour le projet DCPrism-Laravel avec architecture microservices, optimisée pour le développement et la production.

## 🏗️ Architecture Docker

### Services Principaux

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Nginx LB    │    │   Application   │    │     MySQL       │
│   (Port 80/443) │────│   Laravel       │────│   (Port 3306)   │
│                 │    │   (Port 8000)   │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                       ┌─────────────────┐    ┌─────────────────┐
                       │      Redis      │    │   Queue Worker  │
                       │   (Port 6379)   │────│   (3 workers)   │
                       │                 │    │                 │
                       └─────────────────┘    └─────────────────┘
```

### Services de Support

- **MailHog** (Port 8025) - Test des emails en développement
- **Adminer** (Port 8080) - Administration base de données
- **Redis Commander** (Port 8081) - Interface Redis
- **Grafana** (Port 3000) - Monitoring (production)
- **Prometheus** (Port 9090) - Métriques (production)

## 🚀 Installation Rapide

### Prérequis

- Docker 24.0+
- Docker Compose 2.0+
- Make (optionnel mais recommandé)

### Installation en une commande

```bash
# Clone et installation complète
make install
```

Ou manuellement :

```bash
# Build et démarrage des containers
docker-compose up -d --build

# Installation des dépendances
docker-compose exec app composer install
docker-compose exec app cp .env.docker .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan storage:link

# Migration et seed de la base de données
docker-compose exec app php artisan migrate:fresh --seed
```

## 🌐 Accès aux Services

Après installation, vos services sont accessibles :

- **🌐 Application** : http://localhost:8001
- **⚙️ Interface Admin (Filament)** : http://localhost:8001/admin
- **🗄️ Base de données (Adminer)** : http://localhost:8082
- **📧 Test email (MailHog)** : http://localhost:8026
- **🔴 Cache Redis (Commander)** : http://localhost:8083

## 📋 Commandes Makefile

### Gestion Docker

```bash
make build         # Builder les containers
make up            # Démarrer les services
make down          # Arrêter les services
make restart       # Redémarrer les services
make status        # État des containers
make logs          # Logs de tous les services
make logs-app      # Logs de l'application seulement
```

### Gestion Laravel

```bash
make migrate       # Exécuter les migrations
make migrate-fresh # Migration complète + seeders
make seed          # Exécuter les seeders
make artisan cmd="route:list"  # Commande artisan
make tinker        # Accès au tinker Laravel
make optimize      # Optimiser pour production
```

### Développement

```bash
make shell         # Shell dans le container app
make mysql-shell   # Shell MySQL
make redis-shell   # Shell Redis
make test          # Exécuter les tests
make fix-permissions # Corriger les permissions
```

### Production

```bash
make prod-build    # Build pour production
make prod-up       # Démarrer en production
make prod-deploy   # Déploiement production complet
```

## ⚙️ Configuration Environnements

### Développement (.env.docker)

```bash
APP_ENV=local
APP_DEBUG=true
DB_HOST=mysql
REDIS_HOST=redis
MAIL_HOST=mailhog
```

### Production (.env.production)

```bash
APP_ENV=production
APP_DEBUG=false
DB_HOST=mysql
REDIS_HOST=redis
# + Variables sécurisées via secrets
```

## 🐳 Détails des Containers

### Application (dcprism-app)

**Image :** PHP 8.3 FPM Alpine
**Services :** Nginx + PHP-FPM + Supervisor
**Ports :** 8001:80, 8444:443

**Fonctionnalités :**
- PHP 8.3 avec extensions optimisées
- OPcache avec JIT activé
- Redis pour sessions et cache
- Upload jusqu'à 100MB
- Queue workers automatiques

### Base de Données (dcprism-mysql)

**Image :** MySQL 8.0
**Configuration :** Optimisée pour DCP

**Fonctionnalités :**
- Charset UTF8MB4 
- InnoDB optimisé
- Slow query log
- Backup automatique

### Cache Redis (dcprism-redis)

**Image :** Redis 7.2 Alpine
**Configuration :** Persistance + Mot de passe

**Fonctionnalités :**
- AOF persistance activée
- Limite mémoire avec LRU
- Authentification par mot de passe

### Queue Workers (dcprism-worker)

**Répliques :** 3 workers
**Configuration :** Restart automatique

**Fonctionnalités :**
- Traitement parallèle des jobs
- Retry intelligent des échecs
- Monitoring via Supervisor

## 📊 Monitoring et Observabilité

### Développement

**Health Checks :**
```bash
make health-check  # Vérifier tous les services
curl http://localhost:8000/health  # Health check app
```

**Logs en temps réel :**
```bash
make logs          # Tous les services
make logs-app      # Application seulement
docker stats       # Utilisation ressources
```

### Production

**Services de monitoring inclus :**
- **Prometheus** - Collecte métriques
- **Grafana** - Dashboards visuels
- **Health checks** automatiques

## 🔒 Sécurité

### Développement

- Mots de passe par défaut pour DB/Redis
- Debug activé
- CORS permissif pour développement

### Production

- Variables d'environnement sécurisées
- SSL/TLS configuré
- Rate limiting activé
- Headers de sécurité
- CORS restreint

## 🗃️ Persistance des Données

### Volumes Docker

```yaml
volumes:
  mysql-data:        # Base de données MySQL
  redis-data:        # Cache Redis persistant  
  storage-data:      # Fichiers uploadés Laravel
  logs-data:         # Logs application
  uploads-data:      # Uploads DCP (production)
```

### Backups

```bash
# Backup base de données
make backup-db

# Restore base de données  
make restore-db file="backup.sql"

# Backup automatique (production)
docker-compose --profile backup run db-backup
```

## ⚡ Optimisations Performance

### PHP (php.ini)

- **Memory limit :** 512M
- **Upload max :** 100M
- **Execution time :** 300s
- **OPcache :** Activé avec JIT

### MySQL (my.cnf)

- **InnoDB buffer :** 512M
- **Max connections :** 200
- **Query cache :** Configuré

### Redis

- **Max memory :** 512M  
- **Eviction policy :** LRU
- **Persistance :** AOF

### Nginx

- **Gzip :** Activé
- **Client max body :** 100M
- **Fastcgi buffers :** Optimisés
- **Static files cache :** 1 année

## 🧪 Tests

### Exécution des Tests

```bash
# Tests PHPUnit
make test

# Tests avec couverture
make test-coverage  

# Tests Pest (si installé)
make pest

# Tests dans container isolé
docker-compose exec app php artisan test --env=testing
```

### Configuration Tests

Base de données de test automatiquement créée :
- **DB_DATABASE :** dcprism_test
- **Isolation :** Chaque test en transaction

## 🚀 Déploiement Production

### Build Production

```bash
# Build optimisé pour production
make prod-build

# Déploiement complet
make prod-deploy
```

### Configuration Production

**Multi-stage build :**
1. **Base :** Configuration commune
2. **Development :** Outils de dev + debug
3. **Production :** Optimisé + sécurisé

**Optimisations production :**
- Cache Laravel (config/routes/views)
- Composer optimisé (--no-dev)
- Assets minifiés
- OPcache + JIT maximum

## 🔧 Troubleshooting

### Problèmes Communs

**Permissions de fichiers :**
```bash
make fix-permissions
```

**Cache corrompus :**
```bash
make cache-clear
```

**Base de données :**
```bash
# Reset complet
make migrate-fresh

# Vérifier connexion
make mysql-shell
```

**Queues bloquées :**
```bash
make queue-restart
```

### Debug

**Accès shells :**
```bash
make shell          # Application
make shell-root      # Application (root)
make mysql-shell     # MySQL
make redis-shell     # Redis
```

**Logs détaillés :**
```bash
# Logs temps réel
docker-compose logs -f app

# Logs Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

## 📚 Ressources Utiles

### Documentation

- [Docker Documentation](https://docs.docker.com/)
- [Laravel Docker Best Practices](https://laravel.com/docs/deployment)
- [MySQL Docker Guide](https://hub.docker.com/_/mysql)

### Commandes Utiles

```bash
# Statistiques containers
docker stats

# Nettoyage Docker
make clean          # Nettoyage standard
make clean-all      # Nettoyage complet (ATTENTION!)

# Monitoring
make monitor        # Stats temps réel
```

---

## 🎉 Prêt à l'emploi !

Votre environnement DCPrism-Laravel Dockerisé est maintenant configuré pour :

✅ **Développement rapide** avec hot-reload  
✅ **Tests automatisés** avec base dédiée  
✅ **Monitoring intégré** avec métriques  
✅ **Production ready** avec optimisations  
✅ **Scalabilité** horizontale et verticale  

**Commencez par :** `make install` et accédez à http://localhost:8001 ! 🚀
