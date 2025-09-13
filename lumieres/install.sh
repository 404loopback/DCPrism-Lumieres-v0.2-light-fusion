#!/bin/bash

# =====================================
# Lumières - Installation Script  
# =====================================

set -e

echo "🌟 Installing Lumières Monorepo..."

# Check prerequisites
command -v docker >/dev/null 2>&1 || { echo "❌ Docker required but not installed."; exit 1; }
command -v docker-compose >/dev/null 2>&1 || { echo "❌ Docker Compose required but not installed."; exit 1; }
command -v make >/dev/null 2>&1 || { echo "❌ Make required but not installed."; exit 1; }

# Create environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📋 Creating environment file..."
    cp .env.example .env
    echo "✏️ Please edit .env file with your configuration before continuing."
    echo "📖 Run 'make help' to see available commands."
    exit 0
fi

echo "🚀 Installing dependencies..."
make install

echo "🐳 Starting services..."
make up

echo "⏳ Waiting for services to be ready..."
sleep 30

echo "🧪 Testing installation..."
if curl -f http://localhost:8001 >/dev/null 2>&1; then
    echo "✅ Fresnel is running on http://localhost:8001"
else
    echo "❌ Fresnel failed to start"
fi

if curl -f http://localhost:8000 >/dev/null 2>&1; then
    echo "✅ Meniscus is running on http://localhost:8000"  
else
    echo "❌ Meniscus failed to start"
fi

echo ""
echo "🎉 Installation complete!"
echo ""
echo "📚 Available commands:"
echo "  make help     - Show all available commands"
echo "  make dev      - Start development environment"  
echo "  make logs     - View service logs"
echo "  make down     - Stop all services"
echo ""
echo "🌐 Access URLs:"
echo "  Fresnel:  http://localhost:8001"
echo "  Meniscus: http://localhost:8000" 
echo "  Traefik:  http://localhost:8088"
echo "  Adminer:  http://localhost:8080"
echo ""
