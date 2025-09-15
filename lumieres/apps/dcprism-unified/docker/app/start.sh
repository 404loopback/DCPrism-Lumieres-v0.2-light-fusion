#!/bin/sh

# Script de démarrage pour DCPrism Laravel App
echo "🚀 Starting DCPrism Laravel Application..."

# Synchroniser les UID/GID avec l'hôte si nécessaire
sync_user_permissions() {
    local host_uid=${PUID:-${WWWUSER:-1000}}
    local host_gid=${PGID:-${WWWUSER:-1000}}
    local current_uid=$(id -u www-data)
    local current_gid=$(id -g www-data)
    
    if [ "$host_uid" != "$current_uid" ] || [ "$host_gid" != "$current_gid" ]; then
        echo "🔧 Syncing user permissions: UID=$host_uid, GID=$host_gid"
        groupmod -g "$host_gid" www-data 2>/dev/null || true
        usermod -u "$host_uid" -g "$host_gid" www-data 2>/dev/null || true
        echo "✅ User permissions synchronized"
    fi
}

# Fonction pour vérifier et corriger les permissions
fix_permissions() {
    local path="$1"
    local owner="${2:-www-data:www-data}"
    local mode="${3:-775}"
    
    echo "🔧 Setting permissions for $path (owner: $owner, mode: $mode)..."
    
    # Créer le répertoire s'il n'existe pas
    mkdir -p "$path" 2>/dev/null || true
    
    # Appliquer les permissions de façon récursive
    chown -R "$owner" "$path" 2>/dev/null || true
    
    # Permissions spéciales pour les dossiers Laravel
    if [[ "$path" == *"storage"* ]]; then
        find "$path" -type d -exec chmod 775 {} \; 2>/dev/null || true
        find "$path" -type f -exec chmod 664 {} \; 2>/dev/null || true
    elif [[ "$path" == *"bootstrap/cache"* ]]; then
        find "$path" -type d -exec chmod 775 {} \; 2>/dev/null || true
        find "$path" -type f -exec chmod 664 {} \; 2>/dev/null || true
    else
        chmod -R "$mode" "$path" 2>/dev/null || true
    fi
    
    if [ -w "$path" ]; then
        echo "✅ $path permissions applied successfully"
        return 0
    else
        echo "❌ Warning: Could not fully apply $path permissions, but continuing..."
        return 0  # Ne pas faire échouer le conteneur
    fi
}

# Attendre que la base de données soit prête
echo "⏳ Waiting for database connection..."
until php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB Connected';" 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 2
done

# Vérifier et créer la clé d'application si nécessaire
if [ ! -f "/var/www/.env" ] || ! grep -q "APP_KEY=" /var/www/.env || [ -z "$(grep APP_KEY= /var/www/.env | cut -d'=' -f2)" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Migrations et optimisations Laravel
echo "📊 Running Laravel optimizations..."

# Créer les liens symboliques pour storage
php artisan storage:link --force

# En développement : migrations automatiques
if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    echo "🛠️ Development mode: running migrations..."
    php artisan migrate --force
    
    # Seeders si fichier marker existe
    if [ -f "/var/www/storage/run-seeders" ]; then
        echo "🌱 Running database seeders..."
        php artisan db:seed --force
        rm /var/www/storage/run-seeders
    fi
    
    # Cache de développement
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Publier les assets Telescope (développement)
    echo "🔭 Publishing Telescope assets..."
    php artisan telescope:publish
else
    # Production : optimisations
    echo "🏠 Production mode: optimizing..."
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # En production, on peut désactiver Telescope ou le limiter
    echo "🔭 Telescope configured for production..."
fi

# Synchroniser les permissions utilisateur d'abord
echo "🔒 Synchronizing user permissions with host..."
sync_user_permissions

# Vérifier et corriger les permissions avec fonction réutilisable  
echo "🔒 Setting up critical directories permissions..."
fix_permissions "/var/www/storage" "www-data:www-data" "775"
fix_permissions "/var/www/bootstrap/cache" "www-data:www-data" "775"
fix_permissions "/var/www/public" "www-data:www-data" "755"

# Sanity check
echo "✅ Application ready!"
php artisan --version

# Démarrer Supervisor (Nginx + PHP-FPM)
echo "🌟 Starting Nginx + PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
