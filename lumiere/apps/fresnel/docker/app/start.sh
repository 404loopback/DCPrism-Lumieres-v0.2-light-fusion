#!/bin/sh

# Script de démarrage pour DCPrism Laravel App
echo "🚀 Starting DCPrism Laravel Application..."

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
else
    # Production : optimisations
    echo "🏭 Production mode: optimizing..."
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Vérifier les permissions
echo "🔒 Setting up permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Sanity check
echo "✅ Application ready!"
php artisan --version

# Démarrer Supervisor (Nginx + PHP-FPM)
echo "🌟 Starting Nginx + PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
