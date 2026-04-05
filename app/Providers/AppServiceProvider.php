<?php

namespace App\Providers;

use App\Models\User;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Joaopaulolndev\FilamentEditProfile\Livewire\BrowserSessionsForm;
use Joaopaulolndev\FilamentEditProfile\Livewire\DeleteAccountForm;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditPasswordForm;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm;
use Joaopaulolndev\FilamentEditProfile\Livewire\SanctumTokens;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Model::unguard();
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch
                ->locales(['es', 'en'])
                ->labels([
                    'es' => 'Español',
                    'en' => 'English',
                ]);
        });
        Gate::define('viewPulse', fn (?User $user) => app()->environment('local') || ($user?->status === 'active'));

        Livewire::component('edit_profile_form', EditProfileForm::class);
        Livewire::component('edit_password_form', EditPasswordForm::class);
        Livewire::component('delete_account_form', DeleteAccountForm::class);
        Livewire::component('browser_sessions_form', BrowserSessionsForm::class);
        Livewire::component('sanctum_tokens', SanctumTokens::class);
    }
}
