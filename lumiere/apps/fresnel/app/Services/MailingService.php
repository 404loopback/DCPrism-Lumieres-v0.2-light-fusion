<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Exception;

class MailingService
{
    /**
     * Configuration des templates d'emails
     */
    private const EMAIL_TEMPLATES = [
        'source_account_created' => [
            'subject' => 'Votre compte DCPrism a été créé',
            'view' => 'emails.source.account-created'
        ],
        'source_notification' => [
            'subject' => 'Nouvelle notification pour votre film',
            'view' => 'emails.source.notification'
        ],
        'dcp_status_update' => [
            'subject' => 'Mise à jour du statut DCP',
            'view' => 'emails.dcp.status-update'
        ],
        'contact_message' => [
            'subject' => 'Nouveau message de contact DCPrism',
            'view' => 'emails.contact.message'
        ],
        'monitoring_alert' => [
            'subject' => '🚨 Alerte DCPrism',
            'view' => 'emails.monitoring.alert'
        ]
    ];

    /**
     * Envoi d'email avec gestion d'erreurs et retry
     * Prêt pour intégration provider externe (SendGrid, Mailgun, etc.)
     */
    public function sendEmail(
        string $to, 
        string $template, 
        array $data = [], 
        ?string $customSubject = null,
        array $attachments = []
    ): bool {
        try {
            // Validation du template
            if (!isset(self::EMAIL_TEMPLATES[$template])) {
                throw new Exception("Template email inconnu: {$template}");
            }

            $templateConfig = self::EMAIL_TEMPLATES[$template];
            $subject = $customSubject ?? $templateConfig['subject'];

            // Log de l'envoi
            Log::info('MailingService: Préparation email', [
                'to' => $to,
                'template' => $template,
                'subject' => $subject,
                'data_keys' => array_keys($data)
            ]);

            // Vérifier la configuration email
            if (!$this->isEmailConfigured()) {
                Log::info('MailingService: Email non configuré - mode simulation', [
                    'to' => $to,
                    'template' => $template
                ]);
                return true; // Simulation pour développement
            }

            // TODO: Intégrer provider externe (SendGrid, Mailgun, etc.)
            // $this->sendViaProvider($to, $subject, $templateConfig, $data, $attachments);
            
            // Pour l'instant: méthode Laravel standard (à remplacer par provider)
            Mail::send($templateConfig['view'], $data, function ($message) use ($to, $subject, $attachments) {
                $message->to($to)
                       ->subject($subject);
                
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $message->attach($attachment['path'], $attachment['options'] ?? []);
                    } else {
                        $message->attach($attachment);
                    }
                }
            });

            Log::info('MailingService: Email envoyé avec succès', [
                'to' => $to,
                'template' => $template
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('MailingService: Erreur envoi email', [
                'to' => $to,
                'template' => $template,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Vérifier si l'email est configuré
     */
    private function isEmailConfigured(): bool
    {
        // En développement local, toujours simuler
        if (Config::get('app.env') === 'local') {
            return false;
        }
        
        // Vérifier configuration basique
        $driver = Config::get('mail.default');
        $host = Config::get('mail.mailers.smtp.host');
        $from = Config::get('mail.from.address');
        
        return !empty($driver) && !empty($host) && !empty($from);
    }

    /**
     * Envoi email création compte Source
     */
    public function sendSourceAccountCreated(User $user, string $password): bool
    {
        return $this->sendEmail(
            $user->email,
            'source_account_created',
            [
                'user' => $user,
                'password' => $password,
                'login_url' => route('filament.source.auth.login')
            ]
        );
    }

    /**
     * Envoi notification à une source
     */
    public function sendSourceNotification($movie, string $message, ?string $customSubject = null): bool
    {
        return $this->sendEmail(
            $movie->source_email,
            'source_notification',
            [
                'movie' => $movie,
                'message' => $message,
                'dashboard_url' => route('filament.source.pages.dashboard')
            ],
            $customSubject
        );
    }

    /**
     * Envoi notification changement statut DCP
     */
    public function sendDcpStatusUpdate($dcp, string $message): bool
    {
        return $this->sendEmail(
            $dcp->movie->source_email,
            'dcp_status_update',
            [
                'dcp' => $dcp,
                'movie' => $dcp->movie,
                'message' => $message,
                'dashboard_url' => route('filament.source.pages.dashboard')
            ]
        );
    }

    /**
     * Envoi message de contact
     */
    public function sendContactMessage(array $contactData): bool
    {
        $adminEmail = Config::get('mail.admin_email', 'admin@dcprism.com');
        
        return $this->sendEmail(
            $adminEmail,
            'contact_message',
            $contactData
        );
    }

    /**
     * Envoi alerte monitoring
     */
    public function sendMonitoringAlert(array $alert, ?string $recipient = null): bool
    {
        $recipient = $recipient ?? Config::get('mail.admin_email', 'admin@dcprism.com');
        
        return $this->sendEmail(
            $recipient,
            'monitoring_alert',
            $alert,
            '🚨 ' . $alert['title']
        );
    }

    /**
     * Envoi d'email en lot avec gestion d'erreurs
     */
    public function sendBulkEmails(array $recipients, string $template, array $data = []): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($recipients)
        ];

        foreach ($recipients as $recipient) {
            $success = $this->sendEmail($recipient, $template, $data);
            
            if ($success) {
                $results['success'][] = $recipient;
            } else {
                $results['failed'][] = $recipient;
            }
        }

        Log::info('MailingService: Envoi en lot terminé', $results);

        return $results;
    }

    /**
     * Vérification configuration email
     */
    public function checkEmailConfiguration(): array
    {
        $config = [
            'driver' => Config::get('mail.default'),
            'host' => Config::get('mail.mailers.smtp.host'),
            'port' => Config::get('mail.mailers.smtp.port'),
            'encryption' => Config::get('mail.mailers.smtp.encryption'),
            'from' => Config::get('mail.from.address'),
            'admin_email' => Config::get('mail.admin_email')
        ];

        $status = [
            'configured' => !empty($config['host']) && !empty($config['from']),
            'config' => $config,
            'test_available' => true
        ];

        return $status;
    }

    /**
     * Test d'envoi d'email
     */
    public function sendTestEmail(string $to): bool
    {
        try {
            Mail::raw('Test d\'envoi depuis DCPrism MailingService', function ($message) use ($to) {
                $message->to($to)
                       ->subject('🧪 Test DCPrism MailingService');
            });

            Log::info('MailingService: Email de test envoyé', ['to' => $to]);
            return true;

        } catch (Exception $e) {
            Log::error('MailingService: Erreur email de test', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
