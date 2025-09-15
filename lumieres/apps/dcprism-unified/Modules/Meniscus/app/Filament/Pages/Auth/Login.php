<?php

namespace Modules\Meniscus\app\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected string $view = 'meniscus::filament.pages.auth.login';
}
