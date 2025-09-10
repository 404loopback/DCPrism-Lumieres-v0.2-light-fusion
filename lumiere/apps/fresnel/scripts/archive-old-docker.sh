#!/bin/bash

# =============================================================================
# Script d'Archivage des Anciennes Architectures Docker
# =============================================================================

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Fonctions d'affichage
log_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_header() { echo -e "${CYAN}📦 $1${NC}"; }

# Configuration
ARCHIVE_DIR="archive/legacy-docker-$(date +%Y%m%d_%H%M%S)"

log_header "Archivage des Anciennes Architectures Docker DCPrism"
echo "=================================================================="

# Créer le dossier d'archive
log_info "Création du dossier d'archive: $ARCHIVE_DIR"
mkdir -p "$ARCHIVE_DIR"

# Archiver les Dockerfiles
log_info "Archivage des Dockerfiles..."
if [ -f "Dockerfile" ]; then
    cp "Dockerfile" "$ARCHIVE_DIR/Dockerfile.original"
    log_success "Dockerfile original archivé"
fi

if [ -f "Dockerfile.optimized" ]; then
    cp "Dockerfile.optimized" "$ARCHIVE_DIR/Dockerfile.optimized"
    log_success "Dockerfile optimisé archivé"
fi

# Archiver les docker-compose
log_info "Archivage des fichiers docker-compose..."
if [ -f "docker-compose.yml" ]; then
    cp "docker-compose.yml" "$ARCHIVE_DIR/docker-compose.yml.current"
    log_success "docker-compose.yml actuel archivé"
fi

if [ -f "docker-compose.optimized.yml" ]; then
    cp "docker-compose.optimized.yml" "$ARCHIVE_DIR/docker-compose.optimized.yml"
    log_success "docker-compose optimisé archivé"
fi

if [ -f "docker-compose.prod.yml" ]; then
    cp "docker-compose.prod.yml" "$ARCHIVE_DIR/docker-compose.prod.yml"
    log_success "docker-compose production archivé"
fi

# Archiver les configurations Docker existantes
log_info "Archivage des configurations Docker..."

# Configurations PHP
if [ -d "docker/php" ]; then
    cp -r "docker/php" "$ARCHIVE_DIR/php-configs"
    log_success "Configurations PHP archivées"
fi

# Configurations Supervisor
if [ -d "docker/supervisor" ]; then
    cp -r "docker/supervisor" "$ARCHIVE_DIR/supervisor-configs"
    log_success "Configurations Supervisor archivées"
fi

# Configurations MySQL
if [ -d "docker/mysql" ]; then
    cp -r "docker/mysql" "$ARCHIVE_DIR/mysql-configs"
    log_success "Configurations MySQL archivées"
fi

# Scripts existants
if [ -f "docker-cleanup-and-optimize.sh" ]; then
    cp "docker-cleanup-and-optimize.sh" "$ARCHIVE_DIR/"
    log_success "Script d'optimisation archivé"
fi

# Configurations Nginx (si déjà dans archive)
if [ -d "archive/nginx-configs" ]; then
    cp -r "archive/nginx-configs" "$ARCHIVE_DIR/"
    log_success "Configurations Nginx archivées"
fi

# Créer un fichier de documentation
log_info "Création de la documentation d'archive..."
cat > "$ARCHIVE_DIR/README.md" << 'EOF'
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
EOF

# Créer un script de restauration
log_info "Création du script de restauration..."
cat > "$ARCHIVE_DIR/restore.sh" << EOF
#!/bin/bash
# Script de restauration de l'ancienne architecture Docker

echo "⚠️  Restauration de l'ancienne architecture Docker DCPrism"
echo "============================================================"

read -p "Confirmer la restauration (cela supprimera l'architecture Traefik) ? (y/N): " -n 1 -r
echo
if [[ ! \$REPLY =~ ^[Yy]$ ]]; then
    echo "Restauration annulée"
    exit 1
fi

# Arrêter les services actuels
echo "🛑 Arrêt des services actuels..."
docker-compose down

# Supprimer les nouvelles images
echo "🗑️ Suppression des nouvelles images..."
docker rmi -f dcprism-laravel:latest 2>/dev/null || true
docker rmi -f dcprism-adminer:latest 2>/dev/null || true

# Restaurer les fichiers
echo "📁 Restauration des fichiers..."
cp docker-compose.yml.current ../docker-compose.yml
cp Dockerfile.original ../Dockerfile

# Restaurer les configurations
cp -r php-configs ../docker/php
cp -r supervisor-configs ../docker/supervisor
cp -r nginx-configs/nginx ../docker/nginx

echo "✅ Restauration terminée"
echo "🚀 Redémarrage avec l'ancienne architecture..."
cd ..
docker-compose up -d --build
EOF

chmod +x "$ARCHIVE_DIR/restore.sh"

# Résumé
echo ""
log_success "🎉 Archivage terminé !"
echo ""
echo "📋 Archive créée dans: $ARCHIVE_DIR"
echo ""
echo "📄 Fichiers archivés :"
ls -la "$ARCHIVE_DIR/" | grep -v "^total" | tail -n +2 | while read line; do
    echo "   • $(echo $line | awk '{print $9}')"
done
echo ""
echo "🔄 Pour restaurer l'ancienne architecture :"
echo "   cd $ARCHIVE_DIR && ./restore.sh"
echo ""
echo "📚 Documentation complète disponible dans :"
echo "   $ARCHIVE_DIR/README.md"

log_warning "L'ancienne architecture reste disponible mais n'est plus maintenue"
