# DCPrism Laravel

**Plateforme de gestion des Digital Cinema Packages (DCP) pour festivals de cinéma**

<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo"></a>
<a href="https://filamentphp.com" target="_blank"><img src="https://github.com/filamentphp/filament/raw/3.x/art/logo.svg" width="200" alt="Filament Logo"></a>
</p>

<p align="center">
<img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel">
<img src="https://img.shields.io/badge/Filament-4.x-F59E0B?style=flat-square&logo=php" alt="Filament">
<img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php" alt="PHP">
<img src="https://img.shields.io/badge/Migration-85%25-4CAF50?style=flat-square" alt="Migration Progress">
</p>

---

## ✅ **État du Projet - Septembre 2025**

**DCPrism Laravel** est la nouvelle version moderne de la plateforme DCPrism, migrée de **Lumen/Vue.js** vers **Laravel 12 + Filament 4**.

### 🎯 **Migration 85% Complétée**
- ✅ **Architecture & Infrastructure** : 100% opérationnelle
- ✅ **Services Critiques** : 100% migrés (Upload B2, Nomenclature, Analyse DCP) 
- ✅ **Interface Multi-Panels** : 100% fonctionnelle (6 panels)
- ✅ **Sécurité & Autorisations** : 100% implémentée
- ✅ **Observabilité & Monitoring** : 100% intégrée
- 🟡 **Tests & Documentation** : 70% terminés

### 📊 **Statut Technique**
- **Stack** : Laravel 12.26.2 + Filament 4.0.4 + PHP 8.3
- **Base** : SQLite (dev) + MySQL (prod) + Redis (cache)
- **Storage** : Backblaze B2 configuré + Upload multipart
- **Queues** : 5 queues spécialisées DCP opérationnelles 
- **Panels** : Admin, Manager, Source, Tech, Cinema, Supervisor

### 📚 **Documentation**

| Document | Description | Statut |
|----------|-------------|--------|
| **[Migration Overview](./docs/migration/README.md)** | Index et roadmap migration | ✅ À jour |
| **[Migration Report](./docs/migration/MIGRATION_REPORT.md)** | Rapport technique complet | ✅ Septembre |
| **[Workflows Métier](./docs/migration/WORKFLOWS_METIER.md)** | Processus business | ✅ Validé |

---

## 🎬 **À Propos de DCPrism**

**DCPrism** est un **système de Print Traffic multi-rôles** pour festivals de cinéma. 

**📋 Workflows Métier** : [Documentation complète](./docs/migration/WORKFLOWS_METIER.md)

### **Rôles Utilisateurs**
- 👨‍💻 **SuperAdmin** : Gestion globale festivals + assignation Managers
- 🎪 **Manager Festival** : Création films/versions + gestion comptes Sources
- 📤 **Source** : Sélection versions + upload DCP multipart
- 🔧 **Technicien** : Validation manuelle + contrôle qualité

### **Fonctionnalités Clés**
- 🎪 **Configuration par festival** : Nomenclature personnalisable
- 📤 **Upload multipart Backblaze** : Frontend-only, un répertoire/version
- 🤖 **Analyse externe automatisée** : Rapport conformité post-upload
- ✅ **Validation relationnelle** : Compatibilité DCP/Salle (futur)

### 🔧 **Stack Technique**

- **Backend** : Laravel 12.x (PHP 8.3+)
- **Interface Admin** : Filament 4.x
- **Base de données** : MySQL 8.0
- **Cache/Sessions** : Redis
- **Storage** : Backblaze B2/S3
- **Conteneurisation** : Docker

---

## 🐳 **Docker & Développement**

### **Services Déployés**
| Service | Port | Description |
|---------|------|-------------|
| **Application** | 8001 | Laravel + Filament 4 |
| **MySQL** | 3308 | Base de données |
| **Redis** | 6381 | Cache et sessions |
| **Adminer** | 8082 | Interface admin BDD |
| **MailHog** | 8026 | Test d'emails |
| **Redis Commander** | 8083 | Interface admin Redis |

### **Commandes Docker**
```bash
# Démarrer l'environnement
docker-compose up -d

# Voir les logs
docker-compose logs -f app

# Arrêter
docker-compose down

# Commandes Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
```

### **URLs de Développement**
- **Application** : http://localhost:8001
- **Admin Filament** : http://localhost:8001/admin
- **Base de données** : http://localhost:8082 (Adminer)
- **Emails** : http://localhost:8026 (MailHog)
- **Redis** : http://localhost:8083 (Redis Commander)

### **Identifiants par défaut**
- **Admin** : `admin@dcprism.local` / `admin123`
- **MySQL** : `dcprism` / `dcprism_password`

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
