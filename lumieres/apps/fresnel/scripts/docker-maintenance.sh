#!/bin/bash

# =============================================================================
# Script de Maintenance Docker pour DCPrism-Laravel
# =============================================================================

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
log_error() { echo -e "${RED}❌ $1${NC}"; }
log_header() { echo -e "${CYAN}🔧 $1${NC}"; }

# Fonction d'aide
show_help() {
    echo "🔧 Script de Maintenance Docker DCPrism-Laravel"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  status       Afficher l'état des services"
    echo "  logs         Afficher les logs récents"
    echo "  health       Vérifier la santé des services"
    echo "  cleanup      Nettoyer les ressources Docker"
    echo "  restart      Redémarrer tous les services"
    echo "  update       Mettre à jour les images"
    echo "  backup       Sauvegarder les données"
    echo "  scale        Scaler les workers"
    echo "  monitor      Mode monitoring continu"
    echo "  help         Afficher cette aide"
    echo ""
}

# Fonction status
show_status() {
    log_header "État des Services DCPrism"
    echo ""
    
    # État des containers
    echo "📦 Containers:"
    docker-compose ps
    echo ""
    
    # Utilisation des ressources
    echo "💾 Utilisation des ressources:"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}" | head -10
    echo ""
    
    # Volumes
    echo "💿 Volumes persistants:"
    docker volume ls | grep dcprism
    echo ""
    
    # Images
    echo "🖼️ Images utilisées:"
    docker images | grep -E "(dcprism|traefik|mariadb|redis)" | head -10
}

# Fonction logs
show_logs() {
    log_header "Logs Récents"
    
    if [ -n "$2" ]; then
        SERVICE="$2"
        log_info "Logs pour le service: $SERVICE"
        docker-compose logs --tail=50 -f "$SERVICE"
    else
        log_info "Logs globaux (dernières 50 lignes par service)"
        docker-compose logs --tail=50
    fi
}

# Fonction health check
check_health() {
    log_header "Vérification de Santé des Services"
    echo ""
    
    # Vérifier Traefik
    log_info "Test Traefik Dashboard..."
    if curl -f -s http://localhost:8080/ping >/dev/null 2>&1; then
        log_success "Traefik: OK"
    else
        log_error "Traefik: PROBLÈME"
    fi
    
    # Vérifier l'application
    log_info "Test Application Laravel..."
    if curl -f -s -k https://dcprism.local >/dev/null 2>&1; then
        log_success "Laravel App: OK"
    else
        log_error "Laravel App: PROBLÈME"
    fi
    
    # Vérifier la base de données
    log_info "Test Base de Données..."
    if docker-compose exec -T mariadb mysqladmin ping -h localhost -u root -proot_password >/dev/null 2>&1; then
        log_success "MariaDB: OK"
    else
        log_error "MariaDB: PROBLÈME"
    fi
    
    # Vérifier Redis
    log_info "Test Redis..."
    if docker-compose exec -T redis redis-cli -a redis_password ping >/dev/null 2>&1; then
        log_success "Redis: OK"
    else
        log_error "Redis: PROBLÈME"
    fi
    
    # Health checks Docker
    echo ""
    log_info "Health Checks Docker:"
    docker-compose ps --filter "health=unhealthy" | grep -v "^Name" | while read line; do
        if [ -n "$line" ]; then
            log_error "Service non-sain détecté: $line"
        fi
    done
    
    # Vérifier l'espace disque
    echo ""
    log_info "Espace disque Docker:"
    docker system df
}

# Fonction cleanup
cleanup_docker() {
    log_header "Nettoyage Docker"
    
    read -p "Confirmer le nettoyage Docker (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_warning "Nettoyage annulé"
        return
    fi
    
    log_info "Arrêt des containers..."
    docker-compose down
    
    log_info "Nettoyage des images non utilisées..."
    docker image prune -f
    
    log_info "Nettoyage des volumes non utilisés..."
    docker volume prune -f
    
    log_info "Nettoyage des réseaux non utilisés..."
    docker network prune -f
    
    log_info "Nettoyage du cache de build..."
    docker builder prune -f
    
    log_success "Nettoyage terminé"
    
    log_info "Redémarrage des services..."
    docker-compose up -d
}

# Fonction restart
restart_services() {
    log_header "Redémarrage des Services"
    
    if [ -n "$2" ]; then
        SERVICE="$2"
        log_info "Redémarrage du service: $SERVICE"
        docker-compose restart "$SERVICE"
    else
        log_info "Redémarrage de tous les services..."
        docker-compose restart
    fi
    
    log_success "Services redémarrés"
}

# Fonction update
update_images() {
    log_header "Mise à Jour des Images"
    
    log_info "Téléchargement des dernières images..."
    docker-compose pull
    
    log_info "Reconstruction des images personnalisées..."
    docker-compose build --pull
    
    log_info "Redémarrage avec les nouvelles images..."
    docker-compose up -d
    
    log_success "Images mises à jour"
}

# Fonction backup
backup_data() {
    log_header "Sauvegarde des Données"
    
    BACKUP_DIR="./backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    
    log_info "Sauvegarde de la base de données..."
    docker-compose exec -T mariadb mysqldump -u root -proot_password dcprism > "$BACKUP_DIR/database.sql"
    
    log_info "Sauvegarde des volumes Docker..."
    docker run --rm -v dcprism-laravel_storage-data:/data -v "$PWD/$BACKUP_DIR":/backup alpine tar czf /backup/storage-data.tar.gz -C /data .
    docker run --rm -v dcprism-laravel_logs-data:/data -v "$PWD/$BACKUP_DIR":/backup alpine tar czf /backup/logs-data.tar.gz -C /data .
    
    log_info "Sauvegarde de la configuration..."
    cp docker-compose.yml "$BACKUP_DIR/"
    cp .env "$BACKUP_DIR/" 2>/dev/null || true
    
    log_success "Sauvegarde créée dans: $BACKUP_DIR"
}

# Fonction scale
scale_workers() {
    log_header "Scaling des Workers"
    
    if [ -z "$2" ]; then
        log_error "Usage: $0 scale <nombre_workers>"
        exit 1
    fi
    
    WORKERS="$2"
    log_info "Scaling à $WORKERS workers..."
    docker-compose up -d --scale worker="$WORKERS"
    
    log_success "Workers scalés à $WORKERS instances"
}

# Fonction monitor
monitor_services() {
    log_header "Mode Monitoring Continu"
    log_info "Appuyez sur Ctrl+C pour arrêter"
    
    while true; do
        clear
        echo "🔄 DCPrism Monitoring - $(date)"
        echo "=================================="
        
        # Status rapide
        docker-compose ps --format "table {{.Name}}\t{{.State}}\t{{.Status}}"
        echo ""
        
        # Ressources
        docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}" | head -10
        echo ""
        
        # Logs d'erreur récents
        echo "📋 Logs d'erreur récents:"
        docker-compose logs --since 1m | grep -i error | tail -5 || echo "Aucune erreur récente"
        
        sleep 30
    done
}

# Menu principal
case "${1:-help}" in
    "status")
        show_status
        ;;
    "logs")
        show_logs "$@"
        ;;
    "health")
        check_health
        ;;
    "cleanup")
        cleanup_docker
        ;;
    "restart")
        restart_services "$@"
        ;;
    "update")
        update_images
        ;;
    "backup")
        backup_data
        ;;
    "scale")
        scale_workers "$@"
        ;;
    "monitor")
        monitor_services
        ;;
    "help"|*)
        show_help
        ;;
esac
