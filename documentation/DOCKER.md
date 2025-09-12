# DCPrism Docker Architecture

## 🚀 Quick Start

```bash
# Full stack (apps + dev tools)
docker-compose up -d

# Applications only
docker-compose --profile apps up -d

# Development tools only  
docker-compose --profile dev up -d

# Production (no dev tools)
docker-compose --profile prod up -d

# Infrastructure tools
docker-compose --profile infrastructure up -d
```

## 📋 Services (14 containers)

### Applications
- **fresnel-app** - Film management (port 8001)
- **meniscus-app** - Event management (port 8000)
- **fresnel-worker/scheduler** - Background jobs
- **meniscus-worker** - Background jobs

### Infrastructure
- **mariadb** - Database (port 3306)
- **redis** - Cache (port 6379)
- **traefik** - Reverse proxy (port 80, 8088)

### Development Tools
- **adminer** - Database UI (port 8080)
- **mailpit** - Email testing (port 8025)
- **redis-commander** - Redis UI (port 8081)

### Infrastructure Tools
- **opentofu** - Infrastructure as Code
- **ansible** - Automation
- **infisical** - Secrets management (port 3000)

## 🌐 URLs

- **Fresnel**: http://fresnel.localhost
- **Meniscus**: http://meniscus.localhost  
- **Traefik Dashboard**: http://traefik.localhost
- **Adminer**: http://adminer.localhost
- **Mailpit**: http://mailpit.localhost
- **Redis**: http://redis.localhost
- **Infisical**: http://localhost:3000

## 🔧 Commands

```bash
# Build images manually
./build.sh

# View logs
docker-compose logs [service]

# Access shell
docker-compose exec [service] bash

# Stop all
docker-compose down

# Rebuild specific service
docker-compose up -d --build [service]
```

## 📁 Structure

```
lumiere/infra/docker/
├── fresnel/docker-compose.yml
├── meniscus/docker-compose.yml  
├── development/docker-compose.yml
├── opentofu/Dockerfile
├── ansible/Dockerfile
└── infisical/Dockerfile
```

## ⚙️ Configuration

Default profiles set in `.env`:
```
COMPOSE_PROFILES=apps,dev
```
