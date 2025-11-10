<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\AttentionRequiredWidget;
use App\Filament\Widgets\BookingCalendarWidget;
use App\Filament\Widgets\BookingStatsWidget;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\LatestActivitiesWidget;
use App\Filament\Widgets\PaymentStatsWidget;
use App\Filament\Widgets\PopularVehiclesWidget;
use App\Filament\Widgets\RecentBookingsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\ReviewStatsWidget;
use App\Filament\Widgets\UserStatsWidget;
use App\Filament\Widgets\VehicleStatsWidget;
use App\Filament\Widgets\VehicleUtilizationWidget;
use App\Http\Middleware\LocalizationMiddleware;
use Filament\Auth\Pages\Login as PagesLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('SENTIENTS A.I')
            ->brandLogo(asset('images/logo.jpg'))
            ->darkModeBrandLogo(asset('images/logo.jpg'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo.jpg'))
            ->login(PagesLogin::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            // Performance optimizations
            ->maxContentWidth('full')
            ->spa()
            ->databaseNotifications()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->renderHook(
                'panels::body.start',
                fn (): string => view('filament.hooks.rtl-support')->render()
            )
            ->renderHook(
                'panels::topbar.end',
                fn (): string => view('filament.hooks.language-switcher')->render()
            )
            ->renderHook(
                'panels::user-menu.start',
                fn (): string => view('filament.hooks.user-menu')->render()
            )
            ->renderHook(
                'panels::styles.after',
                fn (): string => '<link rel="stylesheet" href="'.asset('build/assets/mobile-responsive.css').'">'
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->plugins([
                SpotlightPlugin::make(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                DashboardStatsOverview::class,
                AttentionRequiredWidget::class,
                BookingCalendarWidget::class,
                BookingStatsWidget::class,
                PaymentStatsWidget::class,
                RevenueChartWidget::class,
                ReviewStatsWidget::class,
                VehicleStatsWidget::class,
                VehicleUtilizationWidget::class,
                UserStatsWidget::class,
                LatestActivitiesWidget::class,
                PopularVehiclesWidget::class,
                RecentBookingsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                LocalizationMiddleware::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->resourceCreatePageRedirect('index')
            ->brandName('SENTIENTS A.I')
            ->authGuard('web')
            ->authMiddleware([
                Authenticate::class,
            ]);
        //            ->renderHook(
        //                'panels::styles.before',
        //                fn (): string => '<style>
        //                    [x-cloak] { display: none !important; }
        //
        //                    /* Dashboard Background */
        //                    .fi-main {
        //                        background-image: url("https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=2070&auto=format&fit=crop") !important;
        //                        background-size: cover !important;
        //                        background-position: center !important;
        //                        background-attachment: fixed !important;
        //                        position: relative !important;
        //                    }
        //
        //                    /* Lighter Overlay - 20% instead of 40% */
        //                    .fi-main::before {
        //                        content: "" !important;
        //                        position: fixed !important;
        //                        top: 0 !important;
        //                        left: 0 !important;
        //                        right: 0 !important;
        //                        bottom: 0 !important;
        //                        background: rgba(255, 255, 255, 0.2) !important;
        //                        z-index: 0 !important;
        //                        pointer-events: none !important;
        //                    }
        //
        //                    .dark .fi-main::before {
        //                        background: rgba(0, 0, 0, 0.3) !important;
        //                    }
        //
        //                    /* Ensure content is above overlay */
        //                    .fi-main > * {
        //                        position: relative !important;
        //                        z-index: 1 !important;
        //                    }
        //
        //                    /* Sidebar transparency */
        //                    .fi-sidebar {
        //                        background: rgba(255, 255, 255, 0.96) !important;
        //                        backdrop-filter: blur(12px) !important;
        //                    }
        //
        //                    .dark .fi-sidebar {
        //                        background: rgba(17, 24, 39, 0.96) !important;
        //                        backdrop-filter: blur(12px) !important;
        //                    }
        //
        //                    /* Widget cards with glass effect */
        //                    [class*="fi-wi-"], .fi-section, [class*="fi-ta-"], .fi-fo-field-wrp {
        //                        background: rgba(255, 255, 255, 0.92) !important;
        //                        backdrop-filter: blur(10px) !important;
        //                        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        //                    }
        //
        //                    .dark [class*="fi-wi-"], .dark .fi-section, .dark [class*="fi-ta-"], .dark .fi-fo-field-wrp {
        //                        background: rgba(31, 41, 55, 0.92) !important;
        //                        backdrop-filter: blur(10px) !important;
        //                        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        //                    }
        //                </style>'
        //            );
    }
}
