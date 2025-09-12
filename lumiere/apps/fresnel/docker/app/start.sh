#!/bin/sh

# Script de démarrage pour DCPrism Laravel App
echo "🚀 Starting DCPrism Laravel Application..."

# Fonction pour vérifier et corriger les permissions
fix_permissions() {
    local path="$1"
    local owner="${2:-www-data:www-data}"
    local mode="${3:-775}"
    
    if [ ! -w "$path" ]; then
        echo "🔧 Fixing permissions for $path..."
        chown -R "$owner" "$path" 2>/dev/null || true
        chmod -R "$mode" "$path" 2>/dev/null || true
        
        if [ -w "$path" ]; then
            echo "✅ $path permissions fixed"
            return 0
        else
            echo "❌ Warning: Could not fix $path permissions"
            return 1
        fi
    else
        echo "✅ $path permissions OK"
        return 0
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

# Vérifier et corriger les permissions avec fonction réutilisable
echo "🔒 Checking critical directories permissions..."
fix_permissions "/var/www/storage" "www-data:www-data" "775"
fix_permissions "/var/www/bootstrap/cache" "www-data:www-data" "775"

# Sanity check
echo "✅ Application ready!"
php artisan --version

# Démarrer Supervisor (Nginx + PHP-FPM)
echo "🌟 Starting Nginx + PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
