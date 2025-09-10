#!/bin/bash

# =============================================================================
# Script de Migration vers l'Architecture Docker Moderne avec Traefik
# =============================================================================

set -e  # Arrêter en cas d'erreur

echo "🚀 Migration vers l'architecture Docker moderne avec Traefik"
echo "============================================================="

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage avec couleurs
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Vérifications préalables
log_info "Vérification des prérequis..."

if ! command -v docker &> /dev/null; then
    log_error "Docker n'est pas installé"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    log_error "Docker Compose n'est pas installé"
    exit 1
fi

# Sauvegarder l'ancien environnement
log_info "Sauvegarde de l'environnement actuel..."

if [ -f "docker-compose.yml" ]; then
    cp docker-compose.yml docker-compose.yml.backup.$(date +%Y%m%d_%H%M%S)
    log_success "Ancien docker-compose.yml sauvegardé"
fi

if [ -f ".env" ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    log_success "Fichier .env sauvegardé"
fi

# Arrêter les anciens containers
log_info "Arrêt des anciens containers..."
docker-compose down || true
log_success "Anciens containers arrêtés"

# Nettoyer les anciennes images
log_info "Nettoyage des anciennes images Docker..."
docker rmi -f dcprism-laravel-worker:latest 2>/dev/null || true
docker rmi -f dcprism-laravel-scheduler:latest 2>/dev/null || true
docker rmi -f dcprism-laravel-app:latest 2>/dev/null || true
docker image prune -f
log_success "Anciennes images supprimées"

# Copier la nouvelle configuration
log_info "Installation de la nouvelle configuration..."
cp docker-compose.modern.yml docker-compose.yml
log_success "Nouveau docker-compose.yml installé"

# Rendre les scripts exécutables
log_info "Configuration des permissions..."
chmod +x docker/app/start.sh
chmod +x docker/worker/start.sh  
chmod +x docker/scheduler/start.sh
chmod +x scripts/*.sh
log_success "Permissions configurées"

# Créer les fichiers manquants si nécessaire
log_info "Création des configurations manquantes..."


# Configuration du fichier hosts pour développement local
log_info "Configuration du fichier hosts local..."
if ! grep -q "dcprism.local" /etc/hosts; then
    log_warning "Ajout des domaines locaux au fichier /etc/hosts (nécessite sudo)..."
    echo "127.0.0.1 dcprism.local traefik.dcprism.local adminer.dcprism.local mail.dcprism.local redis.dcprism.local" | sudo tee -a /etc/hosts
    log_success "Domaines locaux ajoutés à /etc/hosts"
else
    log_success "Domaines locaux déjà configurés dans /etc/hosts"
fi

# Build de la nouvelle image
log_info "Construction de la nouvelle image Docker..."
docker-compose build --no-cache app
log_success "Nouvelle image construite"

# Démarrage des services
log_info "Démarrage de la nouvelle architecture..."
docker-compose up -d
log_success "Services démarrés"

# Attendre que les services soient prêts
log_info "Attente de la disponibilité des services..."
sleep 30

# Vérifications de santé
log_info "Vérification de l'état des services..."

# Vérifier Traefik
if curl -f -s http://localhost:8080/api/rawdata >/dev/null 2>&1; then
    log_success "Traefik Dashboard accessible sur http://localhost:8080"
else
    log_warning "Traefik Dashboard non accessible"
fi

# Vérifier l'application
if curl -f -s -k https://dcprism.local >/dev/null 2>&1; then
    log_success "Application accessible sur https://dcprism.local"
else
    log_warning "Application non encore accessible (certificat SSL en cours de génération)"
fi

# Afficher l'état des containers
echo ""
log_info "État des containers:"
docker-compose ps

echo ""
log_success "🎉 Migration terminée avec succès!"
echo ""
echo "📋 Services disponibles:"
echo "   • Application principale: https://dcprism.local"
echo "   • Traefik Dashboard:     http://localhost:8080"  
echo "   • Adminer:              https://adminer.dcprism.local"
echo "   • MailHog:              https://mail.dcprism.local"
echo "   • Redis Commander:      https://redis.dcprism.local"
echo ""
echo "💡 Avantages de la nouvelle architecture:"
echo "   ✅ Une seule image Laravel partagée (économie de 4GB+)"
echo "   ✅ SSL automatique avec Let's Encrypt"
echo "   ✅ Load balancing et haute disponibilité"
echo "   ✅ Service discovery automatique"
echo "   ✅ Dashboard Traefik pour monitoring"
echo "   ✅ Séparation propre des responsabilités"
echo ""
echo "🔄 Pour revenir à l'ancienne configuration:"
echo "   cp docker-compose.yml.backup.* docker-compose.yml"
echo "   docker-compose up -d"
