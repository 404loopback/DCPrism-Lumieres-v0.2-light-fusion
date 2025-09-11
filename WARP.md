# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Overview

DCPrism is a Laravel monorepo for digital film and event management, organized in the `lumiere/` directory. It contains two primary applications:

- **Fresnel**: Film management system for DCP (Digital Cinema Package) metadata management, festival coordination, and screening scheduling
- **Meniscus**: Infrastructure orchestration platform (formerly DCParty) for deploying and managing distributed DCP-o-matic mirrors

The architecture uses **distributed processing**: Fresnel manages film metadata and coordination while Meniscus deploys DCP-o-matic mirrors that handle the actual DCP validation and processing workloads.

Both applications share a common infrastructure stack using Docker Compose with Laravel Sail, MariaDB, Redis, and development tools.

## Architecture

```
DCPrism/
├── lumiere/
│   ├── apps/
│   │   ├── fresnel/          # Film Management (Laravel + Filament)
│   │   └── meniscus/         # Event Management (Laravel + Filament)
│   └── infra/
│       ├── docker/           # Shared Docker configurations
│       ├── nginx/            # Reverse proxy configs
│       └── shared-configs/   # Shared application configs
├── docker-compose.yml        # Main orchestration
├── .env                      # Environment variables
└── WARP.md                  # This file
```

**Application Architecture:**
- Multi-tenant Laravel applications with shared services
- Filament v4 admin panels for both applications
- Multi-database architecture (fresnel, dichroic/meniscus, testing databases)
- Lightweight queue workers for orchestration and coordination (not DCP processing)
- Shared Redis cache and session storage
- **Distributed Processing**: DCP-o-matic mirrors deployed via Meniscus handle heavy DCP workloads

**Processing Flow:**
```
Fresnel (Metadata) → Meniscus (Orchestration) → DCP-o-matic Mirrors (Processing)
     ↓                      ↓                           ↓
- Film metadata        - Deploy mirrors            - DCP validation
- Festival planning    - Monitor health            - Format conversion
- Screening coordination - Load balancing          - Thumbnail generation
```

## Common Development Commands

### Docker Compose Operations
```bash
# Start all services
docker compose up -d

# Build and start (after changes to Dockerfile)
docker compose up --build -d

# Stop all services
docker compose down

# View logs
docker compose logs -f [service-name]
```

### Laravel Sail Commands (Per Application)
```bash
# Start Fresnel development server
cd lumiere/apps/fresnel
./vendor/bin/sail up -d

# Run Artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan filament:upgrade

# Testing
./vendor/bin/sail test
./vendor/bin/sail artisan test --testsuite=Unit
./vendor/bin/sail artisan test --testsuite=Feature
./vendor/bin/sail artisan test --testsuite=API  # Fresnel only

# Code quality
./vendor/bin/sail composer run test  # Runs config:clear + test
./vendor/bin/pint                  # Laravel Pint code formatter
```

### Frontend Development
```bash
# Install dependencies (in each app)
cd lumiere/apps/fresnel
npm install

# Development server with hot reload
npm run dev

# Production build
npm run build
```

### Using Sail Development Scripts
Each application has a `dev` composer script that runs multiple services concurrently:
```bash
cd lumiere/apps/fresnel
./vendor/bin/sail composer run dev
# Starts: server, queue listener, Pail logs, and Vite dev server
```

## Development Workflow

### Initial Setup
1. **Environment Setup:**
   ```bash
   # Copy and configure main environment
   cp .env.example .env
   
   # Copy application-specific environments
   cd lumiere/apps/fresnel && cp .env.example .env
   cd lumiere/apps/meniscus && cp .env.example .env
   ```

2. **Start Infrastructure:**
   ```bash
   # Start shared services (database, redis, mail, etc.)
   docker compose up -d
   ```

3. **Application Setup:**
   ```bash
   # Setup Fresnel
   cd lumiere/apps/fresnel
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate
   ./vendor/bin/sail artisan db:seed
   
   # Setup Meniscus
   cd ../meniscus
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate
   ./vendor/bin/sail artisan db:seed
   ```

### Development Services Access
- **Fresnel App**: http://localhost:8001
- **Meniscus App**: http://localhost:8000
- **Mailpit (Email testing)**: http://localhost:8025
- **Adminer (Database)**: http://localhost:8080
- **Redis Commander**: http://localhost:8081
- **MariaDB**: localhost:3306

### Multi-Database Development
The project uses separate databases:
```bash
# Migrate specific database
./vendor/bin/sail artisan migrate --database=fresnel
./vendor/bin/sail artisan migrate --database=meniscus

# Seed specific database
./vendor/bin/sail artisan db:seed --database=fresnel
```

### Queue Management
Each application runs dedicated **lightweight** queue workers for coordination tasks:

```bash
# View queue status
./vendor/bin/sail artisan queue:work --queue=default

# Process failed jobs
./vendor/bin/sail artisan queue:retry all

# Monitor queue with Pail
./vendor/bin/sail artisan pail
```

**Worker Responsibilities:**
- **Fresnel Workers**: Notifications, reports, API integrations, metadata sync
- **Meniscus Workers**: Infrastructure orchestration, mirror deployment, health monitoring
- **NOT for DCP Processing**: Heavy DCP work is handled by distributed DCP-o-matic mirrors

### Testing Strategy
```bash
# Run all tests for an app
./vendor/bin/sail composer run test

# Run specific test suite
./vendor/bin/sail test --testsuite=Feature

# Run tests with coverage (if configured)
./vendor/bin/sail test --coverage
```

## Architecture Details

### Monorepo Structure
- `lumiere/apps/`: Contains the Laravel applications
- `lumiere/infra/`: Shared infrastructure configurations
- Each app has its own Docker configuration and Sail setup
- Shared services defined in root `docker-compose.yml`

### Container Architecture
Each application follows a **microservices pattern** with dedicated containers:

```yaml
# Per application:
app:        # Nginx + PHP-FPM (web interface)
worker:     # Lightweight background jobs
scheduler:  # Automated maintenance tasks
```

**Worker Container Benefits:**
- **Separation of Concerns**: Web responses vs background tasks
- **Independent Scaling**: Scale workers separately from web servers
- **Fault Tolerance**: Worker crashes don't affect user interface
- **Resource Optimization**: Different CPU/RAM allocation per container type

### Database Strategy
```sql
-- Databases created automatically via init script:
fresnel              -- Fresnel production data
dichroic             -- Meniscus production data (legacy name)
fresnel_testing      -- Fresnel test database
dichroic_testing     -- Meniscus test database
```

### Docker Network Configuration
```yaml
networks:
  dcprism-network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.30.0.0/16
          gateway: 172.30.0.1
```

### Application Communication
Applications can communicate via the Docker network using service names:
- Database: `mariadb:3306`
- Redis: `redis:6379`
- Inter-app communication: `fresnel-app:80`, `meniscus-app:8000`

### Custom Docker Configuration
Fresnel uses a multi-stage Dockerfile with optimized layers:
- **php-base**: Core PHP 8.3 with extensions
- **development**: Dev dependencies and debugging tools
- **production**: Optimized production build

### Environment Variable Matrix
Key environment variables in `.env`:
```bash
# Docker
WWWUSER=1000                    # User ID for containers
SAIL_XDEBUG_MODE=off           # XDebug configuration

# Database connections
DB_HOST=mariadb
FRESNEL_DB_DATABASE=fresnel
DICHROIC_DB_DATABASE=dichroic

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=redis_password

# Application ports
APP_PORT=8001                   # Fresnel
VITE_PORT=5173                 # Vite dev server
```

## Key Technologies & Dependencies

### Backend Stack
- **Laravel 12**: Modern PHP framework with PHP 8.2+ requirement
- **Filament v4**: Admin panel framework with Livewire integration
- **Laravel Sanctum v4**: API authentication
- **Livewire v3.6**: Full-stack reactive framework
- **Laravel Telescope**: Application debugging and monitoring (Fresnel only)

### Database & Caching
- **MariaDB 11**: Primary database with UTF8MB4 support
- **Redis 7.2**: Cache, sessions, and queue backend

### Frontend
- **Vite**: Modern build tool and dev server
- **TailwindCSS v4**: Utility-first CSS framework
- **Laravel Vite Plugin**: Laravel-Vite integration

### Development Tools
- **Laravel Sail**: Docker development environment
- **Laravel Pint**: Code formatting (PHP CS Fixer wrapper)
- **PHPUnit 11**: Testing framework with custom test suites
- **Mailpit**: Local email testing
- **Adminer**: Database administration UI

### DevOps & Infrastructure
- **Docker Compose v2**: Container orchestration
- **Multi-stage Dockerfiles**: Optimized production builds
- **Nginx**: Reverse proxy (production profile)
- **Supervisor**: Process management in containers

### Filament-Specific Dependencies
- **Spatie Laravel ActivityLog**: Audit logging
- **Laravel Swagger (L5 Swagger)**: API documentation (Fresnel only)
- **Blade Icons & Heroicons**: Icon systems for UI

## Distributed Processing Architecture

### DCP-o-matic Mirror Network
DCPrism uses a **distributed architecture** where heavy DCP processing is handled by specialized mirrors:

```
┌─────────────────────────────────────┐
│  DCPrism Core Applications          │
├─────────────────────────────────────┤
│  Fresnel: Film metadata & planning  │
│  Meniscus: Infrastructure orchestr. │
└─────────────────────────────────────┘
           ↓ Orchestrates
┌─────────────────────────────────────┐
│  DCP-o-matic Mirror Network         │
├─────────────────────────────────────┤
│  Mirror 1: DCP validation           │
│  Mirror 2: Format conversion        │
│  Mirror 3: Thumbnail generation     │
│  Mirror N: Distributed processing   │
└─────────────────────────────────────┘
```

### Why Distributed Processing?
- **Performance**: Heavy DCP processing doesn't impact web interface
- **Scalability**: Add mirrors based on workload demand
- **Reliability**: Mirror failures don't affect core applications
- **Geographic Distribution**: Mirrors can be placed closer to cinemas
- **Resource Optimization**: Specialized hardware for DCP processing

### Worker Roles in This Architecture
- **DCPrism Workers**: Lightweight coordination and notification tasks
- **DCP-o-matic Mirrors**: Heavy CPU/GPU intensive DCP processing
- **Clear Separation**: Each component optimized for its specific role

### Recommended Resource Allocation
```yaml
# Lightweight workers for coordination
fresnel-worker:
  cpus: 0.2
  memory: 256M
  
meniscus-worker:  
  cpus: 0.5      # Slightly more for infrastructure tasks
  memory: 512M
  
# Contrast with DCP-o-matic mirrors (deployed separately):
dcp-mirror:
  cpus: 4.0      # Heavy processing
  memory: 8G     # Large files
  gpu: optional  # Hardware acceleration
```

## Application-Specific Notes

### Fresnel (Film Management)
- **Models**: Movies, DCPs, Festivals, Cinemas, Screenings, Nomenclatures
- **API Documentation**: Available via L5 Swagger at `/api/documentation`
- **File Storage**: AWS S3 integration via Laravel Flysystem for metadata and reports
- **Queue Jobs**: 
  - Festival notifications and email campaigns
  - PDF report generation (schedules, invoices)
  - Metadata synchronization with external APIs
  - QR code generation for screenings
  - **NOT DCP Processing**: Handled by distributed DCP-o-matic mirrors

### Meniscus (Infrastructure Orchestration)
- **Models**: Infrastructure Deployments, Jobs, Providers, OpenTofu Configs
- **Focus**: Terraform/OpenTofu-based infrastructure deployment and monitoring
- **Queue Jobs**:
  - Deploy new DCP-o-matic mirrors across regions
  - Health monitoring of distributed mirrors
  - Load balancing and failover management
  - Automated cleanup and maintenance
- **Integration**: Orchestrates the DCP-o-matic mirror network that handles actual DCP processing

### Shared Components
- **Filament Resources**: Located in `app/Filament/` for each application
- **Custom Providers**: Service providers for application-specific functionality
- **Blade Components**: Shared UI components via Blade UI Kit

