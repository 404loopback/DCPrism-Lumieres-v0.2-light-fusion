<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use App\Services\AuditService;

class LogAuthenticationEvents
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle user login event.
     */
    public function handleLogin(Login $event): void
    {
        $this->auditService->logAuthentication(
            $event->user->id,
            'login',
            [
                'guard' => $event->guard,
                'remember' => $event->remember ?? false,
            ]
        );
    }

    /**
     * Handle user logout event.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $this->auditService->logAuthentication(
                $event->user->id,
                'logout',
                [
                    'guard' => $event->guard,
                ]
            );
        }
    }

    /**
     * Handle failed authentication attempt.
     */
    public function handleFailed(Failed $event): void
    {
        $this->auditService->logSecurityEvent(
            'failed_login_attempt',
            [
                'guard' => $event->guard,
                'credentials' => [
                    'email' => $event->credentials['email'] ?? null,
                ],
            ]
        );
    }

    /**
     * Handle password reset event.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->auditService->logAuthentication(
            $event->user->id,
            'password_reset'
        );
    }

    /**
     * Handle email verification event.
     */
    public function handleVerified(Verified $event): void
    {
        $this->auditService->logAuthentication(
            $event->user->id,
            'email_verification'
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events)
    {
        $events->listen(Login::class, [LogAuthenticationEvents::class, 'handleLogin']);
        $events->listen(Logout::class, [LogAuthenticationEvents::class, 'handleLogout']);
        $events->listen(Failed::class, [LogAuthenticationEvents::class, 'handleFailed']);
        $events->listen(PasswordReset::class, [LogAuthenticationEvents::class, 'handlePasswordReset']);
        $events->listen(Verified::class, [LogAuthenticationEvents::class, 'handleVerified']);
    }
}
