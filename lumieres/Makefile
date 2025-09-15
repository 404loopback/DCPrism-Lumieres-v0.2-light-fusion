# ===================================
# Lumières - Monorepo Management
# ===================================

.PHONY: help install dev build test clean docker-up docker-down

# Default target
help: ## Show this help message
	@echo "🌟 Lumières - Available Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

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
dev: ## Start development environment
	@echo "🌟 Starting development environment..."
	docker-compose --profile dev up -d
	npm run dev

dev-stop: ## Stop development environment  
	@echo "🛑 Stopping development environment..."
	docker-compose down

# Building
build: ## Build all applications for production
	@echo "🏗️ Building applications..."
	composer run build
	npm run build

# Testing
test: ## Run all tests
	@echo "🧪 Running tests..."
	composer run test
	npm run test

test-php: ## Run PHP tests only
	@echo "🧪 Running PHP tests..."
	composer run test

test-js: ## Run JavaScript tests only
	@echo "🧪 Running JavaScript tests..."
	npm run test

# Code Quality
lint: ## Run code linting
	@echo "✨ Linting code..."
	composer run lint
	npm run lint

analyse: ## Run static analysis
	@echo "🔍 Analyzing code..."
	composer run analyse

# Cleaning
clean: ## Clean all dependencies and build artifacts
	@echo "🧹 Cleaning..."
	rm -rf vendor node_modules
	rm -rf apps/*/vendor apps/*/node_modules
	rm -rf packages/*/vendor packages/*/node_modules
	composer run clean || true
	npm run clean || true

fresh: ## Fresh install (clean + install)
	@echo "🆕 Fresh installation..."
	make clean
	make install

# Docker shortcuts
up: ## Start all services
	@echo "🚀 Starting all services..."
	docker-compose --profile dev up -d

down: ## Stop all services
	@echo "🛑 Stopping all services..."
	docker-compose down

logs: ## Show services logs
	@echo "📋 Showing logs..."
	docker-compose logs -f

# Application specific
fresnel: ## Work with Fresnel app
	@echo "🎬 Fresnel commands available:"
	@echo "  make fresnel-dev    - Start Fresnel dev server"
	@echo "  make fresnel-test   - Run Fresnel tests"
	@echo "  make fresnel-build  - Build Fresnel"

meniscus: ## Work with Meniscus app
	@echo "🎪 Meniscus commands available:"
	@echo "  make meniscus-dev   - Start Meniscus dev server"  
	@echo "  make meniscus-test  - Run Meniscus tests"
	@echo "  make meniscus-build - Build Meniscus"

fresnel-dev: ## Start Fresnel development
	npm run dev:fresnel

meniscus-dev: ## Start Meniscus development
	npm run dev:meniscus

fresnel-test: ## Test Fresnel app
	composer run test:fresnel
	npm run test:fresnel

meniscus-test: ## Test Meniscus app
	composer run test:meniscus
	npm run test:meniscus

# Status
status: ## Show project status
	@echo "📊 Project Status:"
	@echo "- PHP version: $(shell php --version | head -n 1)"
	@echo "- Node version: $(shell node --version)"
	@echo "- Composer: $(shell composer --version --no-ansi)"
	@echo "- Docker: $(shell docker --version)"
	@echo ""
	@echo "📁 Project structure:"
	@ls -la

# Quick commands
q-dev: up dev ## Quick start: up + dev
q-build: build ## Quick build
q-test: test ## Quick test
q-clean: clean install ## Quick clean + install
