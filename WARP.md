# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

**Lumières** is a comprehensive content management platform for cinematographic and event management, part of the DCPrism ecosystem. The project uses a Laravel-based unified application with modular architecture.

### Core Applications

- **Fresnel** (Module): Film and DCP (Digital Cinema Package) management
- **Meniscus** (Module): Event and festival management
- **Public Site**: Marketing and presentation pages

## Essential Development Commands

### Quick Start
```bash
# Start full development environment
make dev

# Alternative quick start 
make q-dev

# Stop development environment
make dev-stop
```

### Installation & Dependencies
```bash
# Install all dependencies (PHP + Node)
make install

# Fresh installation (clean + install)
make fresh

# Install only PHP dependencies
cd apps/dcprism-unified && composer install

# Install only Node dependencies  
cd apps/dcprism-unified && npm install
```

### Development Environment
```bash
# Start with Docker profiles
docker-compose --profile dev up -d

# Start unified application development server
cd apps/dcprism-unified && composer run dev

# Start Vite dev server for assets
cd apps/dcprism-unified && npm run dev

# View logs
make logs
```

### Testing
```bash
# Run all tests
make test

# PHP tests only
make test-php
cd apps/dcprism-unified && php artisan test

# JavaScript tests only  
make test-js
cd apps/dcprism-unified && npm run test

# Module-specific tests
make fresnel-test
make meniscus-test
```

### Code Quality
```bash
# Lint all code
make lint

# PHP static analysis
make analyse
cd apps/dcprism-unified && composer run analyse

# Laravel Pint formatting
cd apps/dcprism-unified && vendor/bin/pint
```

### Building
```bash
# Build for production
make build
cd apps/dcprism-unified && npm run build
```

## Architecture Overview

### Monorepo Structure
```
lumieres/
├── apps/dcprism-unified/    # Main Laravel application
│   ├── Modules/             # Laravel modules
│   │   ├── Fresnel/         # Film/DCP management
│   │   └── Meniscus/        # Event management
│   ├── app/                 # Core Laravel app
│   ├── resources/           # Views, assets
│   └── public/              # Public web root
├── infra/                   # Docker infrastructure
│   ├── docker/              # Compose files & configs
│   └── ansible/             # Deployment automation
└── scripts/                 # Development scripts
```

### Laravel Modules Architecture

The application uses `nwidart/laravel-modules` for modular organization:

- **Modules/Fresnel/**: Film industry workflows, DCP validation, festival management
- **Modules/Meniscus/**: Event planning, infrastructure jobs, resource management

Each module contains:
- `app/Filament/`: Admin panel resources and pages
- `app/Http/Controllers/`: Module controllers
- `app/Models/`: Eloquent models
- `resources/views/`: Blade templates
- `routes/`: Module-specific routes

### Filament Panel System

Multiple specialized admin panels based on user roles:

**Public Access:**
- `/` - Homepage
- `/features` - Features showcase
- `/about` - About page
- `/contact` - Contact form

**Admin Panels:**
- `/fresnel` - Primary Fresnel panel (admin access)
- `/meniscus` - Meniscus panel (admin access)

**Role-based Panels:**
- `/panel/manager` - Manager/supervisor access
- `/panel/tech` - Technical operations
- `/panel/cinema` - Cinema operations
- `/panel/source` - Content source management

### Key Technologies

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vite 7, TailwindCSS 4, Alpine.js via Filament
- **Admin Interface**: FilamentPHP 4 with multiple panels
- **Database**: MariaDB (via Docker)
- **Cache**: Redis (via Docker)
- **Asset Pipeline**: Vite with Laravel plugin
- **Authentication**: Laravel Sanctum
- **File Management**: Spatie Media Library
- **Authorization**: Filament Shield (roles/permissions)

## Development Environment Setup

### Docker Profiles

The project uses Docker Compose profiles for different environments:

```bash
# Development (includes dev tools, hot reload)
docker-compose --profile dev up -d

# Production (optimized, SSL via Traefik)  
docker-compose --profile prod up -d

# Architecture tools (includes analysis tools)
docker-compose --profile architecture up -d
```

### Local Laravel Setup

```bash
cd apps/dcprism-unified

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Link storage
php artisan storage:link

# Generate Filament user
php artisan make:filament-user
```

### Module Development

When working with modules:

```bash
# Generate new module
php artisan module:make ModuleName

# Generate module components
php artisan module:make-controller ControllerName ModuleName
php artisan module:make-model ModelName ModuleName

# Module-specific commands
php artisan module:list
php artisan module:enable ModuleName
php artisan module:disable ModuleName
```

## Testing Strategy

### Running Specific Tests

```bash
# Single test file
cd apps/dcprism-unified && php artisan test tests/Feature/SpecificTest.php

# Test with coverage
cd apps/dcprism-unified && php artisan test --coverage

# Module tests (if organized by module)
cd apps/dcprism-unified && php artisan test --group=fresnel
cd apps/dcprism-unified && php artisan test --group=meniscus
```

### Database Testing

```bash
# Refresh test database
cd apps/dcprism-unified && php artisan migrate:fresh --seed --env=testing
```

## Debugging & Monitoring

### Laravel Debugging
```bash
# Real-time logs with Pail
cd apps/dcprism-unified && php artisan pail

# Queue monitoring  
cd apps/dcprism-unified && php artisan queue:listen

# Tinker REPL
cd apps/dcprism-unified && php artisan tinker
```

### Performance Monitoring
```bash
# Clear various caches
cd apps/dcprism-unified && php artisan optimize:clear

# View application status
make status
```

## Common Development Patterns

### Filament Resource Creation
When creating new Filament resources, follow the module-based organization:
- Place resources in `Modules/{ModuleName}/app/Filament/{PanelName}/Resources/`
- Use appropriate panel providers for registration
- Follow existing naming conventions for consistency

### Database Changes
- Always create migrations within the appropriate module
- Use model factories for consistent test data
- Follow Laravel naming conventions for tables and columns

### Asset Management
- Module-specific assets should be organized under `resources/`
- Use Vite's module loading system for optimal bundle splitting
- TailwindCSS classes are globally available across modules

## Infrastructure Notes

### Docker Services
- **Traefik**: Reverse proxy with automatic SSL (production)
- **MariaDB**: Primary database (persistent storage)
- **Redis**: Cache and session storage
- **Scheduler**: Laravel task scheduling
- **Queue Worker**: Background job processing

### Port Configuration
- **Development**: http://localhost (Traefik routing)
- **Direct Laravel**: http://localhost:8000 (when running `php artisan serve`)
- **Vite HMR**: http://localhost:5173 (hot reload during development)

## Troubleshooting

### Common Issues
- **Permission errors**: Ensure proper ownership of `storage/` and `bootstrap/cache/` directories
- **Module not found**: Run `composer dump-autoload` and verify `modules_statuses.json`
- **Asset compilation**: Clear Vite cache and restart dev server
- **Database connection**: Verify Docker services are running with `docker-compose ps`

### Reset Commands
```bash
# Nuclear reset (careful!)
make clean
docker-compose down -v  # Removes volumes
make install
```
