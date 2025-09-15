<?php

namespace Modules\Fresnel\app\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Log;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

class Login extends BaseLogin
{
    protected string $view = 'fresnel::panel.login';
    
    public function mount(): void
    {
        Log::info('Fresnel Custom Login: mount() called');
        parent::mount();
    }
    
    public function authenticate(): ?LoginResponse
    {
        Log::info('Fresnel Custom Login: authenticate() called', [
            'data' => $this->form->getState()
        ]);
        
        try {
            $response = parent::authenticate();
            Log::info('Fresnel Custom Login: authentication successful');
            return $response;
        } catch (\Throwable $e) {
            Log::error('Fresnel Custom Login: authentication failed', [
                'error' => $e->getMessage(),
                'data' => $this->form->getState()
            ]);
            throw $e;
        }
    }
}
