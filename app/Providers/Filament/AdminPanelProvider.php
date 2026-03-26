<?php

namespace App\Providers\Filament;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use Andreia\FilamentUiSwitcher\FilamentUiSwitcherPlugin;
use App\Filament\Auth\Login as FilamentLogin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->globalSearch(true)
            ->globalSearchDebounce('600ms')
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->globalSearchFieldSuffix('Ctrl+K')
            ->id('admin')
            ->path('admin')
            ->maxContentWidth(Width::Full)
            ->login(FilamentLogin::class)
            ->profile(false)
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('panel.nav.bookings')),
                NavigationGroup::make()
                    ->label(__('panel.nav.loans')),
                NavigationGroup::make()
                    ->label(__('panel.nav.admin')),
                NavigationGroup::make()
                    ->label(__('panel.nav.settings')),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('90s')
            ->plugins([
                FilamentUiSwitcherPlugin::make()->withModeSwitcher(),
                FilamentApexChartsPlugin::make(),
                FilamentShieldPlugin::make(),
                ActivityLogPlugin::make()
                    ->label(__('panel.activity_log.label'))
                    ->pluralLabel(__('panel.activity_log.plural'))
                    ->navigationGroup(__('panel.nav.admin')),
                FilamentEditProfilePlugin::make()
                    ->slug('mi-perfil')
                    ->setTitle(__('panel.profile.title'))
                    ->setNavigationLabel(__('panel.profile.navigation'))
                    ->setIcon('heroicon-o-user')
                    ->shouldShowEditProfileForm(true)
                    ->shouldShowEmailForm(true)
                    ->shouldShowEditPasswordForm(false)
                    ->shouldShowDeleteAccountForm(true)
                    ->shouldShowBrowserSessionsForm(true)
                    ->shouldShowLocaleForm(true, ['es' => 'Español', 'en' => 'English'])
                    ->shouldShowThemeColorForm(true)
                    ->shouldShowMultiFactorAuthentication(true)
                    ->shouldShowSanctumTokens(true)
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024'
                    )
                    ->customProfileComponents([
                        \App\Livewire\CustomProfileComponent::class,
                    ]),

            ])
            ->plugin(
                FilamentFullCalendarPlugin::make()
                    ->schedulerLicenseKey('')
                    ->selectable(true)
                    ->editable(true)
                    ->timezone(config('app.timezone'))
                    ->locale(fn (): string => app()->getLocale())
                    ->plugins(['dayGrid', 'timeGrid'])
                    ->config([
                        'dayMaxEvents' => true,
                        'moreLinkClick' => 'day',
                    ])
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\Reports::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
