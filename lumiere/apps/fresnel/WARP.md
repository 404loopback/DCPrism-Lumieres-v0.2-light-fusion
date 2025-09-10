# DCPrism-Laravel - WARP AI Context

**Stack**: L12+F4 | **Status**: 85% migrated | **Env**: Docker-native

DCP management platform for film festivals with multi-role workflow pipeline.

## Core Architecture

### Multi-Panel Filament System

The application uses 6 specialized Filament panels, each serving different user roles:

- **Admin Panel** (`/panel/admin`) - System administration and global oversight
- **Manager Panel** (`/panel/manager`) - Festival management and configuration
- **Source Panel** (`/panel/source`) - DCP upload and version selection
- **Tech Panel** (`/panel/tech`) - Technical validation and quality control
- **Cinema Panel** (`/panel/cinema`) - Cinema interface (future)
- **Supervisor Panel** (`/panel/supervisor`) - System supervision

Each panel has dedicated providers in `app/Providers/Filament/` and specialized resources in `app/Filament/{Panel}/`.

### Critical Services

- **BackblazeService** - Multipart upload to B2 cloud storage
- **UnifiedNomenclatureService** - Festival-specific DCP naming conventions
- **DcpAnalysisService** - Technical analysis and validation of DCPs
- **AuditService** - GDPR-compliant audit logging
- **MonitoringService** - System metrics and alerting

Services are located in `app/Services/` and registered through dedicated service providers.

### Queue System

5 specialized queues handle different DCP processing stages:
- `dcp_analysis` (30min timeout) - DCP technical analysis
- `dcp_validation` (20min timeout) - Format validation
- `dcp_metadata` (10min timeout) - Metadata extraction
- `dcp_nomenclature` (5min timeout) - Nomenclature generation
- `dcp_batch` (1h timeout) - Batch processing

## Development Commands

**CRITICAL**: Docker-native env with permission-aware dev helper

### Optimized Dev Workflow
```bash
# Environment mgmt
docker-compose up -d        # Start stack
docker-compose down          # Stop stack
docker-compose logs -f app   # Monitor

# Core commands (uses ./dev-commands.sh)
./dev-commands.sh artisan migrate
./dev-commands.sh artisan db:seed  
./dev-commands.sh composer install
./dev-commands.sh npm run build
./dev-commands.sh shell              # www-data shell
./dev-commands.sh fix-permissions    # Fix Docker perms

# Queue mgmt
./dev-commands.sh artisan queue:work
./dev-commands.sh artisan queue:work --queue=dcp_analysis
./dev-commands.sh artisan queue:failed
./dev-commands.sh artisan queue:retry all
docker-compose logs -f worker        # Monitor worker
```

### Permission Solution
**Problem**: Docker creates files as root → permission conflicts  
**Solution**: `dev-commands.sh` executes commands as www-data (UID 1000)  
**Config**: `.env.docker` + rebuild required for UID mapping

### Build Optimization
**Problem**: 400s build time → productivity killer  
**Solutions**: Multi-stage caching, BuildKit, parallel builds  
**Performance**: ~400s → ~60s (85% improvement)  

```bash
# Optimized build commands
./dev-commands.sh build         # Full optimized build
./dev-commands.sh build-fast    # Fast cached rebuild
./dev-commands.sh benchmark     # Compare performance
./dev-commands.sh clean         # Clean cache
```

**Key optimizations**: Separate composer/npm layers, parallel compilation (-j$(nproc)), --chown in COPY, BuildKit enable

## Key Models and Relationships

### Core Models
- **Festival** - Main entity organizing movies and parameters
- **Movie** - Films within festivals with metadata
- **Version** - Different versions of movies (subtitles, formats)
- **Dcp** - Digital Cinema Packages with technical specs
- **User** - Multi-role users (admin, manager, source, tech)

### Critical Relationships
- Festival hasMany Movies
- Movie hasMany Versions
- Version hasOne Dcp
- Festival hasMany Parameters (nomenclature configuration)
- User belongsTo Festival (for managers)

Models include comprehensive relationships, scopes, and observers for business logic.

## Security and Access Control

### Role-Based Access
- **SuperAdmin** - Global system management
- **Manager** - Festival-specific management and user creation
- **Source** - DCP upload and version selection
- **Technician** - Technical validation and quality control

### Policy System
Comprehensive policies control access:
- **MoviePolicy** - Film access based on role and festival context
- **DcpPolicy** - DCP operations with status validation
- **25+ Gates** defined in AuthServiceProvider for fine-grained control

### Festival Context
The `EnsureManagerFestivalSelected` middleware ensures managers have selected their festival context before accessing resources.

## File Storage and Upload

### Backblaze B2 Integration
- Configured as S3-compatible filesystem
- Multipart upload support for large DCP files
- Frontend-only upload with progress callbacks
- Automatic cleanup on failure

### Storage Structure
```
/storage/
  ├── app/uploads/ - Temporary upload staging
  ├── logs/ - Application logs
  └── framework/ - Laravel framework storage
```

External storage on Backblaze B2 organized by festival/movie/version hierarchy.

## Testing

### Test Structure
- Unit tests in `tests/Unit/`
- Feature tests in `tests/Feature/`
- Uses PHPUnit with Laravel testing utilities

### Running Tests (Docker)
```bash
# Run all tests in Docker container
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test tests/Feature/DcpAnalysisTest.php

# Run with coverage
docker-compose exec app php artisan test --coverage

# Alternative using composer
docker-compose exec app composer test
```

## Environment Configuration

**Application Environment: Docker Containers**

### Docker Services (Testing/Development)
- **app** (port 8001) - Main Laravel application container
- **mysql** (port 3308) - MySQL 8.0 database container
- **redis** (port 6381) - Redis cache and session storage container
- **worker** - Dedicated queue processing container
- **scheduler** - Cron job handling container
- **mailhog** (port 8026) - Email testing container
- **adminer** (port 8082) - Database administration web interface
- **redis-commander** (port 8083) - Redis management web interface

### Container Access
```bash
# Access main application container
docker-compose exec app bash

# Access database directly
docker-compose exec mysql mysql -u dcprism -p dcprism

# View container status
docker-compose ps

# View all container logs
docker-compose logs
```

### Key Environment Variables
```env
# Backblaze B2 Configuration
B2_NATIVE_KEY_ID=
B2_NATIVE_APPLICATION_KEY=
B2_BUCKET_NAME=dcp-test

# API Configuration
API_VERSION=v1
WEBHOOK_SECRET=your-webhook-secret-key

# Rate Limiting
API_RATE_LIMIT=1000
UPLOAD_RATE_LIMIT=10
```

## Debugging and Monitoring

### Application Monitoring
- **Telescope** available in development for debugging
- **Pail** for real-time log monitoring (`php artisan pail`)
- Comprehensive audit logging via AuditService
- Business metrics through MonitoringService

### Key Log Files
- `storage/logs/laravel.log` - Application logs
- `storage/logs/audit.log` - Security audit trail
- Queue job logs via database logging

### Dashboard Widgets
Specialized widgets provide real-time insights:
- DCP statistics and processing status
- Storage usage and trends  
- Job monitoring and queue health
- Festival performance metrics

## Business Workflow

### DCP Processing Pipeline
1. **Festival Setup** - Manager configures nomenclature parameters
2. **Movie Creation** - Manager creates films and versions
3. **Source Upload** - Sources select versions and upload DCPs
4. **Automated Analysis** - External service analyzes DCP structure
5. **Technical Validation** - Technicians review and approve/reject
6. **Final Status** - DCP marked as VALID or INVALID

### User Account Management
- SuperAdmin creates Manager accounts
- Managers create Source accounts via email invitation
- Automatic role-based redirection on login

## Migration Notes

This is a migrated codebase from Lumen/Vue.js with 85% completion:

### Completed
- ✅ Full Laravel 12 + Filament 4 infrastructure
- ✅ Multi-panel architecture with role-based access
- ✅ Critical services (Upload, Analysis, Nomenclature)
- ✅ Security and authorization system
- ✅ Queue processing pipeline

### Remaining Work
- Testing coverage completion
- Production configuration (Redis, Horizon)
- User documentation and guides

The architecture is designed for extensibility, with clear separation of concerns and comprehensive service injection.

## Filament 4.x - Breaking Changes

**SysReq**: PHP8.2+ | L11.28+ | TW4.0+ | doctrine/dbal→optional  
**KeyChange**: Unified Schema system replaces separate form/table/infolist systems

### Core Changes

**UnifiedSchema**: `Schema $schema` → replaces separate form/table/infolist  
**Actions**: RecordActionsPosition enum, global Table::configureUsing(), mountedActions injection  
**Tables**: defaultKeySort(false), deferFilters(false) for v3 behavior  
**Forms**: FileUpload→private default, columnSpanFull() not auto  
**Methods**: make(?string $name = null) optional param

### Migration Quick Ref

```bash
# F4 migration commands
composer require filament/upgrade:"^4.0" -W --dev
vendor/bin/filament-v4
php artisan filament:upgrade-directory-structure-to-v4
npx @tailwindcss/upgrade
```

**Breaking Patterns**:
- `getTable*Using()` methods → `$table->*()` config
- FileUpload defaults → private (was public)
- Filters now deferred by default
- Auto primary key sorting enabled

**Global Config Pattern** (AppServiceProvider):
```php
Table::configureUsing(fn (Table $table) => $table->defaultKeySort(false)->deferFilters(false));
FileUpload::configureUsing(fn (FileUpload $upload) => $upload->visibility('public'));
```
