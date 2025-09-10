#!/bin/bash
# Script de restauration de l'ancienne architecture Docker

echo "⚠️  Restauration de l'ancienne architecture Docker DCPrism"
echo "============================================================"

read -p "Confirmer la restauration (cela supprimera l'architecture Traefik) ? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
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
