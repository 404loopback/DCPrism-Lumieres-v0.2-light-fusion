#!/bin/sh

# Script de démarrage pour DCPrism Laravel Worker
echo "⚡ Starting DCPrism Laravel Queue Worker..."

# Attendre que l'application principale soit prête
echo "⏳ Waiting for application to be ready..."
until curl -f http://dcprism-app:9000/fpm-ping 2>/dev/null; do
    echo "Application not ready, waiting..."
    sleep 5
done

# Attendre Redis
echo "⏳ Waiting for Redis connection..."
until php artisan tinker --execute="Redis::ping(); echo 'Redis Connected';" 2>/dev/null; do
    echo "Redis not ready, waiting..."
    sleep 2
done

echo "✅ Dependencies ready, starting queue worker..."

# Configuration de monitoring avec restart automatique
exec php artisan queue:work \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --timeout=90 \
    --memory=512 \
    --queue=default,high,low \
    --verbose
