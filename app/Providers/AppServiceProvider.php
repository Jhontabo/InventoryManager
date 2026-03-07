<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
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
        Model::unguard();
        App::setLocale('es');
        Gate::define('viewPulse', fn (?User $user) => app()->environment('local') || ($user?->status === 'active'));

        Livewire::component('edit_profile_form', \Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm::class);
        Livewire::component('edit_password_form', \Joaopaulolndev\FilamentEditProfile\Livewire\EditPasswordForm::class);
        Livewire::component('delete_account_form', \Joaopaulolndev\FilamentEditProfile\Livewire\DeleteAccountForm::class);
        Livewire::component('browser_sessions_form', \Joaopaulolndev\FilamentEditProfile\Livewire\BrowserSessionsForm::class);
        Livewire::component('sanctum_tokens', \Joaopaulolndev\FilamentEditProfile\Livewire\SanctumTokens::class);
    }
}
