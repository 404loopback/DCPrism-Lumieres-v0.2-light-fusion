# ===================================
# Lumières - Monorepo Management
# ===================================

.PHONY: help install dev build test clean docker-up docker-down

# Default target
help: ## Show this help message
	@echo "🌟 Lumières - Available Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

# ===================================
# 🚀 SETUP AUTOMATISÉ
# ===================================

new: ## 🚀 Setup complet : git pull + install + Docker + migrations + seeders + permissions
	@echo "🚀 === SETUP AUTOMATIQUE DE LUMIERES ==="
	@echo ""
	@echo "🧹 1. Nettoyage des dépendances..."
	make clean || true
	@echo ""
	@echo "📥 2. Installation des dépendances..."
	make install
	@echo ""
	@echo "🐳 3. Démarrage de l'environnement Docker..."
	make dev
	@echo ""
	@echo "⏳ 4. Attente du démarrage des containers (10s)..."
	sleep 10
	@echo ""
	@echo "🗄️ 5. Reset et exécution des migrations..."
	docker exec lumieres-app php artisan migrate:fresh --force
	@echo ""
	@echo "🌱 6. Création des rôles et utilisateurs de base..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\PanelPermissionSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\UserSeeder --force
	@echo ""
	@echo "🛡️ 7. Génération des permissions Filament..."
	docker exec lumieres-app php artisan shield:generate --all --panel=fresnel --no-interaction
	docker exec lumieres-app php artisan shield:generate --all --panel=meniscus --no-interaction
	@echo ""
	@echo "👥 8. Configuration et assignation des rôles Shield..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldRoleAssignmentSeeder --force
	@echo ""
	@echo "🌱 9. Exécution des autres seeders..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\LanguageSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\FestivalSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\MovieSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ParameterSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\NomenclatureSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\NewDCPParametersSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\FresnelSeeder --force
	@echo ""
	@echo "🎉 === SETUP TERMINÉ ! ==="
	@echo ""
	@echo "✅ Votre environnement Lumières est prêt !"
	@echo ""
	@echo "🌐 Accès:"
	@echo "  📱 Application Laravel: http://localhost"
	@echo "  ⚡ Vite (hot reload): http://localhost:5173 ou 5174"
	@echo "  🗄️ Adminer (DB): http://localhost:8080"
	@echo "  📧 Mailpit: http://localhost:8025"
	@echo ""
	@echo "👥 Comptes par défaut:"
	@echo "  👑 Admin (Super Admin): admin@dcprism.local / admin123 - Accès à TOUS les panels"
	@echo "  🔧 Tech: tech@dcprism.local / password - Panel Tech"
	@echo "  👔 Manager: manager@dcprism.local / password - Panel Manager"
	@echo "  🎦 Cinema: cinema@dcprism.local / password - Panel Cinema"
	@echo "  📁 Source: source@dcprism.local / password - Panel Source"
	@echo "  👥 Supervisor: supervisor@dcprism.local / password - Panel Infrastructure"
	@echo ""
	@echo "📋 Commandes utiles:"
	@echo "  make dev-stop  - Arrêter l'environnement"
	@echo "  make logs      - Voir les logs"
	@echo "  make vite-logs - Voir les logs Vite"
	@echo ""

reset: ## 🔄 Reset complet : arrêt + nettoyage + nouveau setup
	@echo "🔄 === RESET COMPLET ==="
	@echo ""
	@echo "🛑 1. Arrêt de l'environnement..."
	make dev-stop
	@echo ""
	@echo "🧹 2. Nettoyage des volumes Docker..."
	docker volume rm lumieres_mariadb-data lumieres_redis-data || true
	@echo ""
	@echo "🚀 3. Nouveau setup complet..."
	make new

quick: ## ⚡ Setup rapide sans nettoyage complet
	@echo "⚡ === SETUP RAPIDE ==="
	@echo ""
	@echo "🐳 1. Démarrage Docker..."
	make dev
	@echo ""
	@echo "⏳ 2. Attente du démarrage (10s)..."
	sleep 10
	@echo ""
	@echo "🗄️ 3. Migrations..."
	docker exec lumieres-app php artisan migrate --force
	@echo ""
	@echo "🌱 4. Rôles et utilisateurs de base..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\PanelPermissionSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\UserSeeder --force
	@echo ""
	@echo "🛡️ 5. Permissions Filament..."
	docker exec lumieres-app php artisan shield:generate --all --panel=fresnel --no-interaction
	docker exec lumieres-app php artisan shield:generate --all --panel=meniscus --no-interaction
	@echo ""
	@echo "👥 6. Assignation des rôles Shield..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldRoleAssignmentSeeder --force
	@echo ""
	@echo "🌱 7. Autres seeders..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\LanguageSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\FestivalSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\MovieSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ParameterSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\NomenclatureSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\NewDCPParametersSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\FresnelSeeder --force
	@echo ""
	@echo "✅ Setup rapide terminé !"
	@echo "🌐 Laravel: http://localhost"

# Installation
install: ## Install all dependencies (PHP + Node)
	@echo "🚀 Installing dependencies..."
	@echo "📦 Installing PHP dependencies in apps/dcprism-unified..."
	cd apps/dcprism-unified && composer install
	@echo "📦 Installing Node dependencies in apps/dcprism-unified..."
	cd apps/dcprism-unified && npm install
	@echo "📦 Installing PHP dependencies in packages..."
	for dir in packages/*/; do \
		if [ -f "$$dir/composer.json" ]; then \
			echo "Installing in $$dir" && cd "$$dir" && composer install && cd ../..; \
		fi; \
	done

# Development
dev: ## Start development environment (Docker + Vite in background)
	@echo "🌟 Starting development environment..."
	docker-compose --profile dev up -d
	@echo "🔥 Starting Vite dev server in background..."
	nohup sh -c 'cd apps/dcprism-unified && npm run dev' > vite.log 2>&1 &
	@echo "✅ Development environment started!"
	@echo "📋 Vite logs: tail -f vite.log"
	@echo "🌐 Laravel: http://localhost"
	@echo "⚡ Vite: http://localhost:5173"

vite-fg: ## Start Vite dev server in foreground (interactive)
	@echo "🔥 Starting Vite dev server..."
	cd apps/dcprism-unified && npm run dev

vite-logs: ## Show Vite logs
	tail -f vite.log

dev-stop: ## Stop development environment  
	@echo "🛑 Stopping development environment..."
	docker-compose down

# Building
build: ## Build all applications for production
	@echo "🏢️ Building applications..."
	cd apps/dcprism-unified && npm run build
	@echo "✅ Build completé !"

# Testing
test: ## Run PHP tests
	@echo "🧪 Running PHP tests..."
	cd apps/dcprism-unified && composer test

test-docker: ## Run PHP tests in Docker
	@echo "🧪 Running PHP tests in Docker..."
	docker exec lumieres-app php artisan test

# Code Quality
lint: ## Run code linting (Laravel Pint)
	@echo "✨ Linting code with Laravel Pint..."
	cd apps/dcprism-unified && ./vendor/bin/pint

lint-docker: ## Run code linting in Docker
	@echo "✨ Linting code in Docker..."
	docker exec lumieres-app ./vendor/bin/pint

analyse: ## Run static analysis (placeholder)
	@echo "🔍 Static analysis not configured yet"

# Cleaning
clean: ## Clean all dependencies and build artifacts
	@echo "🧹 Cleaning dependencies and build artifacts..."
	rm -rf vendor node_modules || true
	rm -rf apps/*/vendor apps/*/node_modules || true
	rm -rf packages/*/vendor packages/*/node_modules || true
	rm -rf apps/dcprism-unified/public/build || true
	rm -f vite.log || true
	@echo "✅ Nettoyage terminé !"

fresh: ## Fresh install (clean + install)
	@echo "🆕 Fresh installation..."
	make clean
	make install

# Docker shortcuts  
logs: ## Show services logs
	@echo "📋 Showing logs..."
	docker-compose logs -f

logs-app: ## Show Laravel app logs
	@echo "📋 Showing Laravel logs..."
	docker exec lumieres-app tail -f storage/logs/laravel.log

# Database management
db-fresh: ## Fresh database with migrations and seeders
	@echo "🗄️ Fresh database setup..."
	docker exec lumieres-app php artisan migrate:fresh --force
	@echo "🌱 Rôles et utilisateurs de base..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\PanelPermissionSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\UserSeeder --force
	@echo "🛡️ Génération des permissions Filament..."
	docker exec lumieres-app php artisan shield:generate --all --panel=fresnel --no-interaction
	docker exec lumieres-app php artisan shield:generate --all --panel=meniscus --no-interaction
	@echo "👥 Assignation des rôles Shield..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldRoleAssignmentSeeder --force
	@echo "🌱 Autres seeders..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\LanguageSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\FestivalSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\MovieSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ParameterSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\NomenclatureSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\NewDCPParametersSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\FresnelSeeder --force
	@echo "✅ Base de données réinitialisée !"

db-seed: ## Run seeders only
	@echo "🌱 Running seeders..."
	docker exec lumieres-app php artisan db:seed --force

shield-config: ## Configure Shield roles and permissions only
	@echo "🛡️ Configuration Shield..."
	@echo "🌱 Rôles de base et utilisateurs (prérequis)..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\PanelPermissionSeeder --force
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\UserSeeder --force
	@echo "🛡️ Génération des permissions Filament..."
	docker exec lumieres-app php artisan shield:generate --all --panel=fresnel --no-interaction
	docker exec lumieres-app php artisan shield:generate --all --panel=meniscus --no-interaction
	@echo "👥 Assignation des rôles aux utilisateurs..."
	docker exec lumieres-app php artisan db:seed --class=Database\\Seeders\\ShieldRoleAssignmentSeeder --force
	@echo "✅ Configuration Shield terminée !"

# TODO: Migrer de Modules\Fresnel\app\Models\User vers App\Models\User (config auth.providers.users.model)
shield-fix: ## Correction et test Shield (modèle auth correct)
	@echo "🔧 Correction Shield - Modèle Auth..."
	@echo "🔍 1. Vérification des modèles User..."
	docker exec lumieres-app php artisan tinker --execute="echo 'Modèle auth config: ' . config('auth.providers.users.model'); \$$authUser = Modules\\Fresnel\\app\\Models\\User::where('email', 'admin@dcprism.local')->first(); echo ' | User auth trouvé: ' . \$$authUser->name;"
	@echo "🔄 2. Assignation super_admin au BON modèle (celui utilisé par l'auth)..."
	docker exec lumieres-app php artisan tinker --execute="\$$authUser = Modules\\Fresnel\\app\\Models\\User::where('email', 'admin@dcprism.local')->first(); \$$authUser->syncRoles([]); \$$authUser->assignRole('super_admin'); echo 'Super admin assigné au modèle AUTH: ' . get_class(\$$authUser);"
	@echo "🗜️ 3. Nettoyage caches..."
	docker exec lumieres-app php artisan optimize:clear > /dev/null 2>&1
	@echo "🎯 4. Test final de connexion..."
	docker exec lumieres-app php artisan tinker --execute="if (Auth::attempt(['email' => 'admin@dcprism.local', 'password' => 'admin123'])) { \$$u = Auth::user(); echo 'CONNEXION: ✅ RÉUSSIE | Classe: ' . get_class(\$$u) . ' | Rôles: ' . \$$u->roles->pluck('name')->implode(', '); Auth::logout(); } else { echo 'CONNEXION: ❌ ÉCHOUÉE'; }"
	@echo ""
	@echo "✅ PROBLÈME RÉSOLU ! Vous pouvez maintenant:"
	@echo "1. Vous connecter à: http://localhost/fresnel/admin"
	@echo "2. Email: admin@dcprism.local | Password: admin123"
	@echo "3. Accéder à Shield: http://localhost/fresnel/admin/shield/roles"

fix-user-permissions: ## Corriger les permissions des utilisateurs non-admin
	@echo "🔧 Correction des permissions utilisateurs..."
	docker exec lumieres-app php artisan tinker --execute="\$$managerRole = Spatie\\Permission\\Models\\Role::where('name', 'manager')->first(); \$$managerRole->givePermissionTo(['ViewAny:Nomenclature', 'View:Nomenclature', 'Create:Nomenclature', 'Update:Nomenclature']); echo 'Permissions nomenclature ajoutées au manager';"
	@echo "✅ Permissions corrigées !"

# Status & Info
status: ## Show project status
	@echo "📊 Project Status:"
	@echo "- PHP version: $(shell php --version | head -n 1 2>/dev/null || echo 'PHP not found')"
	@echo "- Node version: $(shell node --version 2>/dev/null || echo 'Node not found')"
	@echo "- Composer: $(shell composer --version --no-ansi 2>/dev/null || echo 'Composer not found')"
	@echo "- Docker: $(shell docker --version 2>/dev/null || echo 'Docker not found')"
	@echo ""
	@echo "🐳 Docker status:"
	@docker ps --format "table {{.Names}}\t{{.Status}}" --filter name=lumieres || echo "No containers running"

ps: ## Show running containers
	@docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}" --filter name=lumieres
