#!/bin/bash

echo "🧹 Script d'optimisation Docker pour DCPrism-Laravel"
echo "=================================================="

# Arrêter tous les conteneurs DCPrism
echo "🛑 Arrêt des conteneurs existants..."
docker-compose down

# Supprimer les anciennes images DCPrism
echo "🗑️  Suppression des anciennes images DCPrism..."
docker rmi -f dcprism-laravel-worker:latest 2>/dev/null || true
docker rmi -f dcprism-laravel-scheduler:latest 2>/dev/null || true
docker rmi -f dcprism-laravel-app:latest 2>/dev/null || true
docker rmi -f dcprism-laravel-adminer:latest 2>/dev/null || true

# Nettoyer les images orphelines
echo "🧽 Nettoyage des images orphelines..."
docker image prune -f

# Sauvegarder l'ancien docker-compose.yml
if [ -f "docker-compose.yml" ]; then
    echo "💾 Sauvegarde de l'ancien docker-compose.yml..."
    cp docker-compose.yml docker-compose.yml.backup
fi

# Utiliser la version optimisée
echo "⚡ Mise en place de la configuration optimisée..."
cp docker-compose.optimized.yml docker-compose.yml

# Reconstruire avec la nouvelle configuration
echo "🏗️  Reconstruction des images avec la configuration optimisée..."
docker-compose build --no-cache

# Afficher le nouvel état des images
echo "📊 État des images après optimisation:"
docker images | grep -E "(dcprism|REPOSITORY)"

echo ""
echo "✅ Optimisation terminée !"
echo ""
echo "💡 Avantages de l'optimisation :"
echo "   - Une seule image Laravel au lieu de 3 (économie de ~4GB)"
echo "   - Noms d'images harmonisés (dcprism-laravel:latest)"
echo "   - Temps de build réduit pour les services worker/scheduler"
echo "   - Architecture plus maintenable"
echo ""
echo "🚀 Pour démarrer avec la nouvelle configuration :"
echo "   docker-compose up -d"
echo ""
echo "🔄 Pour revenir à l'ancienne configuration :"
echo "   cp docker-compose.yml.backup docker-compose.yml"
