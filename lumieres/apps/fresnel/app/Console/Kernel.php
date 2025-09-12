<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Collecte des métriques toutes les 5 minutes
        $schedule->command('metrics:collect --store --alerts')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/scheduled.log'));

        // Nettoyage des métriques anciennes tous les jours à 2h du matin
        $schedule->command('metrics:cleanup')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();

        // Backup de la base de données tous les jours à 3h du matin
        $schedule->command('backup:run')
                 ->dailyAt('03:00')
                 ->withoutOverlapping();

        // Nettoyage des logs anciens toutes les semaines
        $schedule->command('logs:cleanup')
                 ->weeklyOn(0, '04:00') // Dimanche à 4h
                 ->withoutOverlapping();

        // Nettoyage des logs d'audit selon les règles GDPR
        $schedule->command('audit:cleanup --days=730')
                 ->monthlyOn(15, '03:30') // 15 de chaque mois à 3h30
                 ->withoutOverlapping();

        // Génération de rapports hebdomadaires
        $schedule->command('reports:generate weekly')
                 ->weeklyOn(1, '08:00') // Lundi à 8h
                 ->withoutOverlapping();

        // Génération de rapports mensuels
        $schedule->command('reports:generate monthly')
                 ->monthlyOn(1, '09:00') // 1er du mois à 9h
                 ->withoutOverlapping();

        // Vérification de la santé du système toutes les minutes
        $schedule->command('health:check')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Nettoyage des jobs terminés plus anciens que 7 jours
        $schedule->command('jobs:cleanup')
                 ->dailyAt('01:00')
                 ->withoutOverlapping();

        // Optimisation des DCP - compression et déduplication
        $schedule->command('dcp:optimize')
                 ->dailyAt('05:00')
                 ->withoutOverlapping();

        // Synchronisation des métadonnées avec les sources externes
        $schedule->command('metadata:sync')
                 ->twiceDaily(9, 21) // 9h et 21h
                 ->withoutOverlapping();

        // Traitement des files d'attente prioritaires
        $schedule->command('queue:work --queue=high,default --timeout=300 --sleep=3 --tries=3')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Vérification de l'intégrité des DCP
        $schedule->command('dcp:verify-integrity')
                 ->weeklyOn(6, '06:00') // Samedi à 6h
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
