<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('SUPER_ADMIN_PASSWORD', 'Demo12345!'),
            'remember' => true,
        ]);
    }
}
