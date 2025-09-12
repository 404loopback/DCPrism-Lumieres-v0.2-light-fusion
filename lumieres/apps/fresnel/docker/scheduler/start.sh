#!/bin/sh

# Script de démarrage pour DCPrism Laravel Scheduler
echo "⏰ Starting DCPrism Laravel Scheduler..."

# Attendre que l'application principale soit prête
echo "⏳ Waiting for application to be ready..."
until curl -f http://dcprism-app:9000/fpm-ping 2>/dev/null; do
    echo "Application not ready, waiting..."
    sleep 5
done

echo "✅ Application ready, configuring cron..."

# Créer le fichier cron pour www-data
echo "* * * * * cd /var/www && php artisan schedule:run >> /proc/1/fd/1 2>&1" > /tmp/laravel-cron

# Installer le cron pour www-data
crontab -u www-data /tmp/laravel-cron

# Nettoyer le fichier temporaire
rm /tmp/laravel-cron

echo "🚀 Scheduler configured, starting cron daemon..."

# Démarrer crond avec logging vers stdout
exec crond -f -l 2
