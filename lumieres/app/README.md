# DCPrism Unified

Application unifiée combinant Fresnel (gestion DCP) et Meniscus (gestion événements) avec un site vitrine public.

## Architecture

Cette application Laravel 12 utilise le système de modules Laravel pour organiser le code :

- **Module Fresnel** : Gestion des DCP, validation technique, festivals
- **Module Meniscus** : Gestion d'événements, infrastructure, jobs
- **Site vitrine public** : Pages d'accueil, présentation, contact

## Panels Filament disponibles

### Site public
- **Page d'accueil** : `http://localhost/`
- **Features** : `http://localhost/features`
- **About** : `http://localhost/about`
- **Contact** : `http://localhost/contact`

### Panels d'administration
- **Panel Fresnel** (principal) : `http://localhost/fresnel`
- **Panel Meniscus** : `http://localhost/meniscus`

### Panels spécialisés par rôle
- **Manager** : `http://localhost/panel/manager`
- **Tech** : `http://localhost/panel/tech`  
- **Cinema** : `http://localhost/panel/cinema`
- **Source** : `http://localhost/panel/source`

## Authentification et rôles

Les utilisateurs accèdent aux panels selon leur rôle :
- `admin` → Fresnel + Meniscus
- `manager` → Panel Manager
- `supervisor` → Panel Manager (partagé)
- `tech` → Panel Tech
- `cinema` → Panel Cinema
- `source` → Panel Source

## Démarrage avec Docker

```bash
# Depuis le répertoire lumieres/
./scripts/start-dcprism-unified.sh
```

Ou manuellement :
```bash
# Développement
docker compose --profile dev --profile architecture up -d --build

# Production
docker compose --profile prod --profile architecture up -d --build
```

## Développement

```bash
# Installation des dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Migrations et seed
php artisan migrate --seed

# Serveur de développement
php artisan serve
```

## Structure des modules

```
Modules/
├── Fresnel/        # Module principal DCP
│   ├── app/
│   │   ├── Filament/           # Panels et ressources
│   │   ├── Http/Controllers/   # Contrôleurs
│   │   └── Models/            # Modèles Eloquent
│   ├── resources/views/       # Vues Blade
│   └── routes/               # Routes du module
└── Meniscus/       # Module événements
    ├── app/
    │   ├── Filament/
    │   ├── Http/Controllers/
    │   └── Models/
    ├── resources/views/
    └── routes/
```

## Migration depuis les anciennes applications

Cette application remplace :
- `apps/fresnel/` → Intégré dans `Modules/Fresnel/`
- `apps/meniscus/` → Intégré dans `Modules/Meniscus/`

Les redirections automatiques Traefik permettent la rétrocompatibilité :
- `fresnel.localhost` → `localhost`
- `meniscus.localhost` → `localhost`

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

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
