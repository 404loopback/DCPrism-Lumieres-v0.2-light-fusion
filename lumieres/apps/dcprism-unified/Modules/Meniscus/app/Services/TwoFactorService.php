<?php

namespace Modules\Meniscus\app\Services;

use Modules\Meniscus\app\Models\User;

class TwoFactorService
{
    public function isEnabled($user)
    {
        return false; // Placeholder - 2FA not implemented yet
    }

    public function enable($user, $secret = null)
    {
        return ['success' => false, 'message' => '2FA not implemented yet'];
    }

    public function disable($user)
    {
        return ['success' => false, 'message' => '2FA not implemented yet'];
    }

    public function generateSecret()
    {
        return str()->random(32);
    }

    public function generateQrCode($user, $secret)
    {
        return '';
    }

    public function verify($user, $code)
    {
        return false;
    }

    public function generateBackupCodes($user)
    {
        return [];
    }

    public function useBackupCode($user, $code)
    {
        return false;
    }
}
