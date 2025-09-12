# DCPrism Monorepo Restructuring Plan

## 🎯 Current Problems Analysis

### 1. **Architectural Inconsistencies**
- Docker compose files at root while infrastructure is in `lumiere/infra/`
- Mixed responsibility levels (orchestration + infrastructure)
- Environment configuration scattered across multiple locations

### 2. **Application Organization Issues**
- `fresnel` and `meniscus` are complete Laravel applications (not monorepo packages)
- Each app has its own `artisan`, `composer.json`, vendor dependencies
- Duplicate development tooling and configurations

### 3. **Documentation Fragmentation**
- Root-level `WARP.md` 
- Separate `documentation/` folder
- App-specific documentation in `lumiere/apps/*/docs/`
- Infrastructure docs in `lumiere/infra/`

### 4. **Environment Management Chaos**
- `.env` and `.env.prod` at root
- App-specific `.env` files in each application
- Inconsistent variable naming and organization

---

## 🚀 Proposed New Structure

```
DCPrism/
├── .env.example                    # Template for all environments
├── .env.local                      # Local development (gitignored)
├── .env.production                 # Production template
├── .gitignore
├── README.md                       # Main project documentation
├── docker-compose.yml              # Development orchestration
├── docker-compose.prod.yml         # Production orchestration
├── Makefile                        # Project-wide commands
│
├── docs/                           # Centralized documentation
│   ├── README.md
│   ├── architecture/
│   │   ├── monorepo-structure.md
│   │   ├── docker-architecture.md
│   │   └── database-design.md
│   ├── deployment/
│   │   ├── environments.md
│   │   ├── docker-guide.md
│   │   └── infrastructure.md
│   ├── development/
│   │   ├── getting-started.md
│   │   ├── testing-guide.md
│   │   └── contribution-guide.md
│   └── operations/
│       ├── monitoring.md
│       └── maintenance.md
│
├── apps/                           # Applications
│   ├── fresnel/                    # Film management app
│   │   ├── src/                    # Application source code
│   │   ├── config/                 # App-specific config
│   │   ├── database/
│   │   ├── tests/
│   │   ├── resources/
│   │   └── README.md
│   │
│   └── meniscus/                   # Event management app
│       ├── src/
│       ├── config/
│       ├── database/
│       ├── tests/
│       ├── resources/
│       └── README.md
│
├── packages/                       # Shared packages
│   ├── shared-ui/                  # Common UI components
│   ├── shared-models/              # Shared Eloquent models
│   ├── shared-services/            # Common business logic
│   └── shared-utils/               # Utility functions
│
├── infrastructure/                 # Infrastructure as Code
│   ├── docker/
│   │   ├── images/                 # Custom Docker images
│   │   │   ├── php/
│   │   │   ├── nginx/
│   │   │   └── worker/
│   │   ├── compose/                # Docker compose modules
│   │   │   ├── core.yml            # Database, Redis, etc.
│   │   │   ├── applications.yml    # Apps services
│   │   │   ├── tools.yml           # Development tools
│   │   │   └── monitoring.yml      # Observability stack
│   │   └── configs/                # Service configurations
│   │       ├── nginx/
│   │       ├── mysql/
│   │       └── redis/
│   │
│   ├── terraform/                  # Cloud infrastructure
│   │   ├── environments/
│   │   │   ├── development/
│   │   │   ├── staging/
│   │   │   └── production/
│   │   ├── modules/
│   │   └── shared/
│   │
│   └── ansible/                    # Configuration management
│       ├── playbooks/
│       ├── roles/
│       └── inventories/
│
├── tools/                          # Development & deployment tools
│   ├── scripts/
│   │   ├── setup.sh
│   │   ├── build.sh
│   │   ├── test.sh
│   │   └── deploy.sh
│   ├── ci/
│   │   ├── github-actions/
│   │   └── gitlab-ci/
│   └── monitoring/
│       ├── prometheus/
│       └── grafana/
│
├── tests/                          # Integration tests
│   ├── integration/
│   ├── e2e/
│   └── performance/
│
├── vendor/                         # Shared dependencies (gitignored)
├── node_modules/                   # Frontend dependencies (gitignored)
├── composer.json                   # Root composer (workspace config)
├── package.json                    # Root package.json
├── phpunit.xml                     # Root test configuration
└── .sail/                          # Laravel Sail customizations
```

---

## 📋 Migration Steps

### Phase 1: Environment Cleanup (Priority: Critical)

1. **Consolidate Environment Files**
   ```bash
   # Create centralized environment templates
   mv .env .env.local.backup
   mv .env.prod infrastructure/environments/.env.production
   
   # Create new structure
   mkdir -p infrastructure/environments
   touch .env.example
   touch .env.local
   ```

2. **Fix Docker Compose Structure**
   ```bash
   # Move compose files to logical location
   mkdir -p infrastructure/docker/compose
   mv docker-compose.yml docker-compose.yml.backup
   
   # Create new orchestration structure
   # (detailed files to be created)
   ```

### Phase 2: Application Restructuring (Priority: High)

3. **Transform Applications to Monorepo Packages**
   ```bash
   # Create new apps structure
   mkdir -p apps/fresnel/src
   mkdir -p apps/meniscus/src
   
   # Move application code (preserve Laravel structure but reorganize)
   # This requires careful planning to avoid breaking changes
   ```

4. **Create Shared Packages**
   ```bash
   mkdir -p packages/{shared-ui,shared-models,shared-services,shared-utils}
   # Extract common code from applications
   ```

### Phase 3: Infrastructure Organization (Priority: Medium)

5. **Organize Infrastructure Code**
   ```bash
   # Move existing infra
   mv lumiere/infra infrastructure
   
   # Reorganize structure
   mkdir -p infrastructure/{terraform,ansible}
   ```

6. **Documentation Consolidation**
   ```bash
   mkdir -p docs/{architecture,deployment,development,operations}
   
   # Merge scattered documentation
   mv WARP.md docs/development/
   mv documentation/* docs/operations/
   # Consolidate app-specific docs
   ```

### Phase 4: Tooling & CI/CD (Priority: Low)

7. **Development Tools Setup**
   ```bash
   mkdir -p tools/{scripts,ci,monitoring}
   # Create unified build, test, deploy scripts
   ```

8. **Testing Infrastructure**
   ```bash
   mkdir -p tests/{integration,e2e,performance}
   # Setup cross-application testing
   ```

---

## 🎯 Key Benefits After Restructuring

### 1. **Clear Separation of Concerns**
- Infrastructure code isolated in `infrastructure/`
- Applications properly organized as monorepo packages
- Shared code extracted to `packages/`
- Documentation centralized in `docs/`

### 2. **Simplified Environment Management**
- Single source of truth for environment configuration
- Environment-specific overrides in logical locations
- Consistent variable naming across all services

### 3. **Improved Developer Experience**
- Clear onboarding path with centralized documentation
- Unified build and deployment scripts
- Consistent development environment setup

### 4. **Better CI/CD Pipeline Support**
- Structured for automated testing across apps
- Clear deployment targets and strategies
- Infrastructure as Code properly organized

### 5. **Scalability Preparedness**
- Easy to add new applications
- Shared packages reduce code duplication
- Infrastructure can grow independently

---

## ⚠️ Migration Considerations

### Risk Assessment
- **High Risk**: Moving Laravel applications (database connections, routes, etc.)
- **Medium Risk**: Environment variable reorganization (service discovery changes)
- **Low Risk**: Documentation and infrastructure code movement

### Migration Strategy
1. **Create new structure alongside existing** (no immediate disruption)
2. **Migrate one service at a time** (gradual transition)
3. **Test thoroughly at each step** (maintain functionality)
4. **Update documentation continuously** (keep team informed)

### Rollback Plan
- Keep backup copies of current structure
- Version control every migration step
- Maintain ability to revert to previous state

---

## 🚀 Implementation Timeline

### Week 1-2: Foundation
- [ ] Create new directory structure
- [ ] Consolidate environment configuration
- [ ] Set up new Docker compose architecture

### Week 3-4: Applications
- [ ] Migrate Fresnel application
- [ ] Migrate Meniscus application  
- [ ] Extract shared packages

### Week 5-6: Infrastructure
- [ ] Organize infrastructure code
- [ ] Update deployment scripts
- [ ] Test production deployment

### Week 7-8: Finalization
- [ ] Consolidate documentation
- [ ] Set up monitoring and tooling
- [ ] Final testing and cleanup

---

## 📚 Next Steps

1. **Review and approve this plan**
2. **Create detailed technical specifications for each phase**
3. **Set up development branch for migration work**
4. **Begin with Phase 1 (Environment Cleanup)**
5. **Schedule team training on new structure**

This restructuring will transform DCPrism from an inconsistent collection of projects into a professional, maintainable monorepo that follows industry best practices.
