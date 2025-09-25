# Structure des Pages Filament - DCPrism

## Pages Communes (Tous Rôles) 📋

### `/Modules/Fresnel/app/Filament/Pages/`
- **UserSettingsPage.php** - Paramètres utilisateur (tous panels)
- **AgendaPage.php** - Agenda personnel (tous panels)

## Pages Partagées 🔄

### `/Modules/Fresnel/app/Filament/Shared/Pages/`
- **TeamManagementPage.php** - Gestion équipe (manager, tech, cinema, admin)
- **IncomingFestivalsPage.php** - Festivals à venir (source, manager, tech, cinema, admin)

## Panel Source 📤

### `/Modules/Fresnel/app/Filament/Source/Pages/`
- **HostedMoviesPage.php** - Films hébergés par la source
- **MovieResourcesPage.php** - Ressources des films (assets, documents, versions)

## Panel Manager 🎯

### `/Modules/Fresnel/app/Filament/Manager/Pages/`
- **FestivalSettingsPage.php** - Configuration du festival
- **FestivalResourcesPage.php** - Ressources et documents du festival
- ~~TeamManagementPage~~ → Utilise la page partagée
- ~~IncomingFestivalsPage~~ → Utilise la page partagée

## Panel Tech 🔧

### `/Modules/Fresnel/app/Filament/Tech/Pages/`
- **TechToolsPage.php** - Outils de validation technique
- ~~TeamManagementPage~~ → Utilise la page partagée
- ~~IncomingFestivalsPage~~ → Utilise la page partagée

## Panel Cinema 🎬

### `/Modules/Fresnel/app/Filament/Cinema/Pages/`
- **CinemaSettingsPage.php** - Configuration cinéma
- **ScreenSettingsPage.php** - Gestion des salles et équipements
- ~~TeamManagementPage~~ → Utilise la page partagée
- ~~IncomingFestivalsPage~~ → Utilise la page partagée

## État d'implémentation

### ✅ Créé (structure et logique de base)
- Toutes les pages listées ci-dessus
- Contrôles d'accès par rôle (`shouldRegisterNavigation()`)
- Structure de données et méthodes placeholder

### ⏳ À implémenter
- Views Blade correspondantes
- Tables avec colonnes réelles
- Actions et interactions
- Intégration avec les modèles existants

## Navigation

Les pages s'affichent automatiquement dans la navigation selon :
- **Rôle de l'utilisateur** (via `shouldRegisterNavigation()`)
- **Groupes de navigation** définis
- **Ordre de tri** (`navigationSort`)

---
*Créé le 23/09/2024*
