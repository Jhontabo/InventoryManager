<?php

namespace App\Providers\Filament;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use Andreia\FilamentUiSwitcher\FilamentUiSwitcherPlugin;
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
            ->login()
            ->profile(false)
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Gestión de Reservas'),
                NavigationGroup::make()
                    ->label('Préstamos'),
                NavigationGroup::make()
                    ->label('Administración'),
                NavigationGroup::make()
                    ->label('Configuración'),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('20s')
            ->plugins([
                FilamentUiSwitcherPlugin::make()->withModeSwitcher(),
                FilamentApexChartsPlugin::make(),
                FilamentShieldPlugin::make(),
                ActivityLogPlugin::make()
                    ->label('Auditoria')
                    ->pluralLabel('Auditorias')
                    ->navigationGroup('Administración'),
                FilamentEditProfilePlugin::make()
                    ->slug('mi-perfil')
                    ->setTitle('Mi perfil')
                    ->setNavigationLabel('Mi perfil')
                    ->setIcon('heroicon-o-user')
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowBrowserSessionsForm(false)
                    ->shouldShowEditPasswordForm(false)
                    ->shouldShowEditProfileForm(true)
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
                    ->locale(config('app.locale'))
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
