#!/bin/bash
set -e

echo "🚀 Démarrage de DCPrism Unified"
echo "==============================================="

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis le répertoire racine du projet (lumieres/)"
    exit 1
fi

# Arrêter les anciens services
echo "🛑 Arrêt des anciens services..."
docker compose --profile dev --profile prod down 2>/dev/null || true

# Construire et démarrer l'application unifiée
echo "🔧 Construction de l'image DCPrism Unified..."
docker compose --profile dev --profile architecture up -d --build

echo ""
echo "✅ DCPrism Unified démarré avec succès !"
echo ""
echo "📍 Services disponibles :"
echo "   • Site vitrine public    : http://localhost"
echo "   • Panel Fresnel         : http://localhost/fresnel"
echo "   • Panel Meniscus        : http://localhost/meniscus"
echo ""
echo "🔧 Services de développement :"
echo "   • Application           : http://localhost:8000"
echo "   • Base de données       : localhost:3306"
echo "   • Redis                 : localhost:6379"
echo ""
echo "📝 Redirections automatiques :"
echo "   • http://fresnel.localhost → http://localhost"
echo "   • http://meniscus.localhost → http://localhost"
echo ""
echo "📋 Pour voir les logs: docker compose logs -f dcprism-unified-app"
echo "📋 Pour arrêter: docker compose --profile dev down"
