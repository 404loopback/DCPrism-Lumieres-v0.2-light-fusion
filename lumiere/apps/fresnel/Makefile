.DEFAULT_GOAL := help
.PHONY: help

# Variables
DOCKER_COMPOSE = docker-compose
DOCKER_COMPOSE_PROD = docker-compose -f docker-compose.prod.yml
DOCKER_EXEC = $(DOCKER_COMPOSE) exec app
DOCKER_EXEC_USER = $(DOCKER_COMPOSE) exec --user www app

##@ Docker Management

build: ## Build Docker containers
	$(DOCKER_COMPOSE) build

up: ## Start containers in background
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down

restart: ## Restart containers
	$(DOCKER_COMPOSE) restart

logs: ## Show logs for all containers
	$(DOCKER_COMPOSE) logs -f

logs-app: ## Show application logs
	$(DOCKER_COMPOSE) logs -f app

logs-mysql: ## Show MySQL logs
	$(DOCKER_COMPOSE) logs -f mysql

logs-redis: ## Show Redis logs
	$(DOCKER_COMPOSE) logs -f redis

status: ## Show containers status
	$(DOCKER_COMPOSE) ps

##@ Laravel Management

install: ## Install and setup Laravel application
	make build
	make up
	$(DOCKER_EXEC) composer install
	$(DOCKER_EXEC) cp .env.docker .env
	$(DOCKER_EXEC) php artisan key:generate
	$(DOCKER_EXEC) php artisan storage:link
	make migrate-fresh
	@echo "✅ Application installed successfully!"
	@echo "🌐 Access your application at: http://localhost:8001"
	@echo "🗄️  Database admin at: http://localhost:8082"
	@echo "📧 Mail testing at: http://localhost:8026"

migrate: ## Run database migrations
	$(DOCKER_EXEC) php artisan migrate

migrate-fresh: ## Fresh migrate with seeders
	$(DOCKER_EXEC) php artisan migrate:fresh --seed

seed: ## Run database seeders
	$(DOCKER_EXEC) php artisan db:seed

composer-install: ## Install PHP dependencies
	$(DOCKER_EXEC) composer install

composer-update: ## Update PHP dependencies
	$(DOCKER_EXEC) composer update

npm-install: ## Install Node.js dependencies
	$(DOCKER_EXEC) npm install

npm-build: ## Build frontend assets
	$(DOCKER_EXEC) npm run build

npm-dev: ## Build frontend assets for development
	$(DOCKER_EXEC) npm run dev

##@ Application Commands

shell: ## Access application shell
	$(DOCKER_EXEC) /bin/sh

shell-root: ## Access application shell as root
	$(DOCKER_COMPOSE) exec app /bin/sh

mysql-shell: ## Access MySQL shell
	$(DOCKER_COMPOSE) exec mysql mysql -u dcprism -pdcprism_password dcprism

redis-shell: ## Access Redis shell
	$(DOCKER_COMPOSE) exec redis redis-cli -a redis_password

artisan: ## Run Laravel artisan command (usage: make artisan cmd="migrate")
	$(DOCKER_EXEC) php artisan $(cmd)

tinker: ## Access Laravel tinker
	$(DOCKER_EXEC) php artisan tinker

queue-work: ## Start queue worker
	$(DOCKER_EXEC) php artisan queue:work

queue-restart: ## Restart queue workers
	$(DOCKER_EXEC) php artisan queue:restart

cache-clear: ## Clear all caches
	$(DOCKER_EXEC) php artisan cache:clear
	$(DOCKER_EXEC) php artisan config:clear
	$(DOCKER_EXEC) php artisan route:clear
	$(DOCKER_EXEC) php artisan view:clear

optimize: ## Optimize application for production
	$(DOCKER_EXEC) php artisan config:cache
	$(DOCKER_EXEC) php artisan route:cache
	$(DOCKER_EXEC) php artisan view:cache
	$(DOCKER_EXEC) composer dump-autoload --optimize

##@ Testing

test: ## Run PHPUnit tests
	$(DOCKER_EXEC) php artisan test

test-coverage: ## Run tests with coverage
	$(DOCKER_EXEC) php artisan test --coverage

pest: ## Run Pest tests
	$(DOCKER_EXEC) ./vendor/bin/pest

##@ Development Tools

fix-permissions: ## Fix file permissions
	$(DOCKER_COMPOSE) exec app chown -R www:www /var/www/storage /var/www/bootstrap/cache
	$(DOCKER_COMPOSE) exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

ide-helper: ## Generate IDE helper files
	$(DOCKER_EXEC) php artisan ide-helper:generate
	$(DOCKER_EXEC) php artisan ide-helper:models --write
	$(DOCKER_EXEC) php artisan ide-helper:meta

backup-db: ## Backup database
	$(DOCKER_COMPOSE) exec mysql mysqldump -u dcprism -pdcprism_password dcprism > ./backups/backup_$(shell date +%Y%m%d_%H%M%S).sql

restore-db: ## Restore database (usage: make restore-db file="backup.sql")
	$(DOCKER_COMPOSE) exec -T mysql mysql -u dcprism -pdcprism_password dcprism < $(file)

##@ Production

prod-build: ## Build for production
	$(DOCKER_COMPOSE_PROD) build

prod-up: ## Start production containers
	$(DOCKER_COMPOSE_PROD) up -d

prod-down: ## Stop production containers
	$(DOCKER_COMPOSE_PROD) down

prod-logs: ## Show production logs
	$(DOCKER_COMPOSE_PROD) logs -f

prod-deploy: ## Deploy to production
	$(DOCKER_COMPOSE_PROD) build
	$(DOCKER_COMPOSE_PROD) up -d
	$(DOCKER_COMPOSE_PROD) exec app php artisan migrate --force
	$(DOCKER_COMPOSE_PROD) exec app php artisan config:cache
	$(DOCKER_COMPOSE_PROD) exec app php artisan route:cache
	$(DOCKER_COMPOSE_PROD) exec app php artisan view:cache

##@ Monitoring

monitor: ## Show real-time container stats
	docker stats

health-check: ## Check application health
	curl -f http://localhost:8001/health || echo "❌ Application not responding"
	curl -f http://localhost:8082 || echo "❌ Adminer not responding"
	curl -f http://localhost:8026 || echo "❌ MailHog not responding"

##@ Cleanup

clean: ## Clean up Docker resources
	$(DOCKER_COMPOSE) down -v --remove-orphans
	docker system prune -f

clean-all: ## Clean up everything (DESTRUCTIVE!)
	$(DOCKER_COMPOSE) down -v --remove-orphans --rmi all
	docker system prune -af --volumes

##@ Help

help: ## Display this help message
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
