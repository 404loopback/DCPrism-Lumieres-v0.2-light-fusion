#!/bin/bash
# =============================================================================
# Aliases Sail pour DCPrism Monorepo
# =============================================================================

# Source ce fichier pour avoir les aliases :
# source ./sail-aliases.sh

# Alias pour Fresnel (Film Management)
alias sail-fresnel='cd /home/inad/DCPrism/lumiere/apps/fresnel && ./vendor/bin/sail'

# Alias pour Meniscus (Event Management)
alias sail-meniscus='cd /home/inad/DCPrism/lumiere/apps/meniscus && ./vendor/bin/sail'

# Alias pour démarrer tout le stack
alias dcprism-up='cd /home/inad/DCPrism && docker compose up -d'
alias dcprism-down='cd /home/inad/DCPrism && docker compose down'
alias dcprism-logs='cd /home/inad/DCPrism && docker compose logs -f'

# Fonctions helper
fresnel() {
    cd /home/inad/DCPrism/lumiere/apps/fresnel
    ./vendor/bin/sail "$@"
}

meniscus() {
    cd /home/inad/DCPrism/lumiere/apps/meniscus
    ./vendor/bin/sail "$@"
}

echo "✅ Aliases DCPrism Sail chargés !"
echo "Usage:"
echo "  fresnel artisan migrate     # Fresnel commands"
echo "  meniscus artisan migrate    # Meniscus commands"
echo "  dcprism-up                  # Start all services"
echo "  dcprism-down                # Stop all services"
