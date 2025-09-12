# 🌟 Lumières - DCPrism Project

**Lumières** est un projet de gestion de contenus cinématographiques et d'événements faisant partie de l'écosystème DCPrism.

## 🎯 Applications

### 🎬 **Fresnel** - Film Management
Application de gestion des contenus cinématographiques et DCP.

- **Port de développement**: 8001
- **Base de données**: fresnel
- **Documentation**: [docs/development/fresnel/](docs/development/)

### 🎪 **Meniscus** - Event Management  
Application de gestion des événements et festivals.

- **Port de développement**: 8002
- **Base de données**: meniscus  
- **Documentation**: [apps/meniscus/README.md](apps/meniscus/README.md)

## 📚 Documentation

### 🏗️ [Architecture](docs/architecture/)
- Structure du monorepo
- Architecture des applications
- Diagrammes et schémas techniques

### 🚀 [Déploiement](docs/deployment/)
- Guides d'installation Docker
- Configuration des environnements
- Procédures de mise en production

### 💻 [Développement](docs/development/)
- Getting started
- Guides de développement
- Tests et debugging

### 🔧 [Opérations](docs/operations/)
- Maintenance et monitoring
- Optimisations
- Troubleshooting

## 🛠️ Structure du projet

```
lumiere/
├── apps/                    # Applications
│   ├── fresnel/            # Gestion cinématographique
│   └── meniscus/           # Gestion d'événements
├── packages/               # Packages partagés
├── infrastructure/         # Infrastructure as Code
├── tools/                  # Outils de développement
├── tests/                  # Tests d'intégration
└── docs/                   # Documentation centralisée
```

## 🚀 Quick Start

```bash
# Démarrage en mode développement
docker-compose --profile dev up -d

# Accès aux applications
# Fresnel: http://localhost:8001
# Meniscus: http://localhost:8002
```

## 🏗️ Statut du projet

- ✅ **Applications opérationnelles** (Fresnel, Meniscus)
- 🔄 **En cours**: Restructuration monorepo (branche: light-fusion)
- 📋 **Planifié**: Packages partagés et tooling unifié

---

*Partie du projet [DCPrism](../README.md)*
