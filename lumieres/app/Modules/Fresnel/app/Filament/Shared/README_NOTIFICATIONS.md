# Système de notifications DCPrism

## Vue d'ensemble

Le système de notifications DCPrism utilise **Filament 4.0.4** avec l'architecture **Shared** pour fournir un système de notifications unifié et réutilisable à travers tous les panels de l'application.

## 🌟 Fonctionnalités

- ✅ **Notifications toast** Filament natives (apparition temporaire)
- ✅ **Notifications persistantes** stockées en base de données
- ✅ **Support des contextes festival** avec filtrage automatique
- ✅ **Filtrage par rôle** utilisateur (admin, tech, manager, supervisor, source, cinema)
- ✅ **Actions personnalisées** dans les notifications (boutons, liens)
- ✅ **Icônes et couleurs** prédéfinies par type
- ✅ **Widget centre de notifications** pour l'interface
- ✅ **Architecture Shared** réutilisable dans tous les panels
- ✅ **Intégration Docker** avec Redis pour les queues

## 📦 Structure

```
app/Filament/Shared/
├── Concerns/
│   └── HasNotifications.php           # Trait principal pour les notifications
├── Services/
│   ├── NotificationManager.php        # Manager (optionnel, legacy)
│   └── NotificationExampleService.php # Service d'exemples
├── Widgets/
│   └── NotificationCenterWidget.php   # Widget centre de notifications
└── README_NOTIFICATIONS.md           # Cette documentation

app/Notifications/
├── TestNotification.php              # Notification de test
├── JobFailedNotification.php         # Notification job échoué (mise à jour)
└── JobCompletedNotification.php      # Notification job terminé (mise à jour)

resources/views/filament/shared/widgets/
└── notification-center.blade.php     # Vue du widget centre

app/Filament/Manager/Pages/
└── NotificationDemoPage.php          # Page de démonstration

resources/views/filament/manager/pages/
└── notification-demo-page.blade.php  # Vue page de démo
```

## 🚀 Utilisation

### 1. Trait HasNotifications

Le trait `HasNotifications` est le cœur du système. Il doit être utilisé dans vos classes Filament (Pages, Resources, etc.).

```php
<?php

namespace App\Filament\Manager\Pages;

use Filament\Pages\Page;
use App\Filament\Shared\Concerns\HasNotifications;

class MyPage extends Page
{
    use HasNotifications;

    public function someAction()
    {
        // Notifications simples (toast)
        $this->notifySuccess('Succès !', 'L\'action a été exécutée');
        $this->notifyWarning('Attention', 'Vérifiez cette configuration');
        $this->notifyError('Erreur', 'Quelque chose s\'est mal passé');
        $this->notifyInfo('Information', 'Voici une info importante');

        // Notifications métier
        $this->notifyJobCompleted('Analyse DCP', 'Terminé avec succès');
        $this->notifyJobFailed('Validation', 'Fichier corrompu');
        $this->notifyMovieUploaded('Mon Film', '/admin/movies/123');
        $this->notifyDcpReady('Mon Film', '/admin/dcps/456');

        // Notifications avec contexte festival
        $this->sendFestivalNotification('Titre', 'Message', 'info', '/url');

        // Notifications persistantes
        $this->sendPersistentNotification('Titre', 'Message', 'info', $user);
    }
}
```

### 2. Notifications par rôle

```php
// Notifier tous les administrateurs
$this->notifyAdmins('Maintenance programmée', 'Le système sera indisponible demain');

// Notifier l'équipe technique
$this->notifyTechnicalTeam('Problème serveur', 'Performance dégradée détectée');

// Notifier par rôle spécifique
$this->notifyUsersByRole('manager', 'Nouveau rapport', 'Disponible dans la section rapports');
```

### 3. Notifications personnalisées

```php
use Filament\Notifications\Actions\Action;

$this->sendCustomNotification(
    'Action requise',
    'Plusieurs films nécessitent validation',
    'heroicon-o-exclamation-triangle',
    'warning',
    [
        Action::make('validate_all')
            ->label('Valider tout')
            ->button()
            ->color('success')
            ->url('/admin/validate-all'),
        Action::make('view_list')
            ->label('Voir la liste')
            ->button()
            ->outlined()
            ->url('/admin/pending-validation')
    ]
);
```

### 4. Notifications Laravel traditionnelles

```php
use App\Notifications\JobCompletedNotification;

$user->notify(new JobCompletedNotification([
    'movie' => $movie,
    'job_type' => 'validation',
    'data' => []
]));
```

### 5. Widget centre de notifications

Pour afficher le widget dans vos panels :

```php
// Dans votre PanelProvider
use App\Filament\Shared\Widgets\NotificationCenterWidget;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->widgets([
            NotificationCenterWidget::class,
            // autres widgets...
        ]);
}
```

## 🎨 Types de notifications

### Types prédéfinis

| Type | Icône | Couleur | Usage |
|------|--------|---------|-------|
| `success` | `heroicon-o-check-circle` | Vert | Opérations réussies |
| `warning` | `heroicon-o-exclamation-triangle` | Orange | Avertissements |
| `error` | `heroicon-o-x-circle` | Rouge | Erreurs |
| `info` | `heroicon-o-information-circle` | Bleu | Informations |
| `job_completed` | `heroicon-o-check-badge` | Vert | Jobs terminés |
| `job_failed` | `heroicon-o-exclamation-circle` | Rouge | Jobs échoués |
| `movie_uploaded` | `heroicon-o-film` | Bleu | Films uploadés |
| `dcp_ready` | `heroicon-o-play` | Vert | DCP prêts |
| `festival_announcement` | `heroicon-o-megaphone` | Orange | Annonces |

### Personnalisation

Vous pouvez étendre les types en modifiant la méthode `getNotificationTypes()` dans le trait :

```php
protected function getNotificationTypes(): array
{
    return array_merge(parent::getNotificationTypes(), [
        'custom_type' => [
            'icon' => 'heroicon-o-star',
            'color' => 'purple'
        ]
    ]);
}
```

## 🔒 Permissions et rôles

### Configuration par rôle

Les canaux de notification sont configurés par rôle dans `HasNotifications` :

```php
private const ROLE_NOTIFICATION_CONFIG = [
    'admin' => ['database', 'mail'],      // Admins : tout
    'tech' => ['database', 'mail'],       // Tech : tout
    'manager' => ['database', 'mail'],    // Managers : tout  
    'supervisor' => ['database'],         // Supervisors : base seulement
    'source' => ['database', 'mail'],     // Sources : tout
    'cinema' => ['database'],             // Cinemas : base seulement
];
```

### Vérifications d'accès

Le trait utilise `HasRoleBasedAccess` pour vérifier les permissions :

```php
if ($this->hasManagementAccess()) {
    $this->notifyWarning('Accès management', 'Fonctions avancées disponibles');
}

if ($this->isAdmin()) {
    $this->notifySuccess('Accès admin', 'Toutes les fonctionnalités débloquées');
}
```

## 🏗️ Architecture technique

### Base de données

Utilise la table `notifications` standard de Laravel :

```bash
# Migration déjà exécutée
docker-compose exec app php artisan migrate
```

### Queues

Les notifications peuvent être mises en queue via Redis :

```php
// Les notifications implémentant ShouldQueue sont automatiquement mises en queue
class JobCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    // ...
}
```

### Docker

Le système utilise l'infrastructure Docker existante :

- **Redis** : Cache et queues
- **Worker** : Traite les jobs de notification
- **MailHog** : Tests d'emails en développement

## 🧪 Test et démonstration

### Page de démonstration

Accédez à la page de démonstration dans le panel Manager :
- URL : `/manager/notification-demo-page`
- Navigation : "Outils" → "Demo Notifications"

### Tests manuels

```php
// Dans une console Tinker
docker-compose exec app php artisan tinker

// Test simple
use App\Filament\Shared\Services\NotificationExampleService;
$service = new NotificationExampleService();
$service->demonstrateNotificationSystem();

// Test notification utilisateur
$user = App\Models\User::first();
$user->notify(new App\Notifications\TestNotification([
    'title' => 'Test manuel',
    'message' => 'Notification depuis Tinker'
]));
```

## 📊 Widget centre de notifications

### Fonctionnalités

- **Statistiques** : Total, non lues, taux de lecture, aujourd'hui
- **Actions** : Marquer comme lu, supprimer, vider les lues
- **Filtrage** : Par contexte festival
- **Interface** : Design Filament natif responsive

### Intégration

```php
// Dans vos widgets de panel
protected function getHeaderWidgets(): array
{
    return [
        NotificationCenterWidget::class,
    ];
}
```

## 🔧 Configuration

### Variables d'environnement

```bash
# Dans .env.docker (déjà configuré)
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=redis_password

# Mail (optionnel pour les notifications email)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### Personnalisation

Créez votre propre trait étendant `HasNotifications` :

```php
<?php

namespace App\Filament\MyPanel\Concerns;

use App\Filament\Shared\Concerns\HasNotifications as BaseHasNotifications;

trait HasCustomNotifications
{
    use BaseHasNotifications;

    protected function notifyCustomSuccess(string $message): void
    {
        $this->sendCustomNotification(
            'Opération réussie',
            $message,
            'heroicon-o-sparkles',
            'success'
        );
    }
}
```

## 🚦 Bonnes pratiques

### 1. Utilisation des types appropriés

```php
// ✅ Bon
$this->notifyJobCompleted('Analyse DCP', 'Terminé');

// ❌ À éviter
$this->notifySuccess('Job terminé', 'Analyse DCP terminé');
```

### 2. Messages informatifs

```php
// ✅ Bon
$this->notifyMovieUploaded($movie->title, "/admin/movies/{$movie->id}");

// ❌ Trop vague
$this->notifyInfo('Opération', 'Terminé');
```

### 3. Gestion des erreurs

```php
// ✅ Bon
try {
    // opération...
    $this->notifySuccess('Fichier traité', 'Le DCP est maintenant disponible');
} catch (Exception $e) {
    $this->notifyError('Erreur de traitement', $e->getMessage());
}
```

### 4. Notifications persistantes pour les actions importantes

```php
// ✅ Bon pour les actions importantes
$this->sendPersistentNotification(
    'Compte créé',
    'Votre accès a été configuré',
    'success',
    $newUser
);

// ✅ Toast pour les actions simples
$this->notifySuccess('Paramètre sauvegardé');
```

## 📝 Extension

### Ajouter de nouveaux types

1. **Étendre le trait** :

```php
protected function getNotificationTypes(): array
{
    return array_merge(parent::getNotificationTypes(), [
        'screening_scheduled' => [
            'icon' => 'heroicon-o-calendar',
            'color' => 'indigo'
        ]
    ]);
}
```

2. **Ajouter des méthodes helpers** :

```php
protected function notifyScreeningScheduled(string $movieTitle, string $date): void
{
    $this->buildNotification(
        'screening_scheduled',
        'Projection programmée',
        "Le film \"{$movieTitle}\" sera projeté le {$date}"
    )->send();
}
```

### Créer de nouveaux canaux

Vous pouvez étendre Laravel pour ajouter d'autres canaux (Slack, Discord, SMS, etc.) :

```php
// config/notifications.php (à créer)
return [
    'channels' => [
        'database' => 'Notifications internes',
        'mail' => 'Email',
        'slack' => 'Slack',
        'discord' => 'Discord'
    ]
];
```

## ⚡ Performance

### Optimisations

- **Queues** : Les notifications lourdes sont automatiquement mises en queue
- **Cache** : Redis utilisé pour le cache des sessions et données
- **Lazy Loading** : Les relations des notifications sont chargées à la demande

### Monitoring

```php
// Surveiller les performances
$stats = $this->getNotificationStats();
// Retourne : total, unread, read, today, this_week, read_rate
```

---

## 🎯 Résumé

Le système de notifications DCPrism offre :

1. **Simplicité** : Trait `HasNotifications` facile à utiliser
2. **Flexibilité** : Toast + persistant + par rôle + par festival
3. **Intégration native** : Filament 4.0.4 + Docker + Redis
4. **Extensibilité** : Architecture Shared modulaire
5. **Interface riche** : Widget centre avec statistiques

**Pour commencer** : Ajoutez `use HasNotifications` dans vos classes Filament et utilisez `$this->notifySuccess('Titre', 'Message')` !

**Page de test** : Allez sur `/manager/notification-demo-page` pour voir toutes les fonctionnalités en action.
