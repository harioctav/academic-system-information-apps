<?php

namespace App\Providers\Filament;

use App\Filament\Resources\DistrictResource;
use App\Filament\Resources\ProvinceResource;
use App\Filament\Resources\RegencyResource;
use App\Filament\Resources\Shield\RoleResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Auth\EditProfile;
use App\Filament\Resources\VillageResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->id('admin')
      ->path('admin')
      ->login()
      ->profile(EditProfile::class)
      ->colors([
        'primary' => '#3498DB',
        'danger' => Color::Rose,
        'gray' => Color::Gray,
        'info' => Color::Sky,
        'success' => Color::Emerald,
        'warning' => Color::Orange,
        'secondary' => '#2C3E50'
      ])
      ->font('Poppins')
      ->brandLogo(fn() => view('components.filament.logo'))
      ->favicon(asset('assets/images/logos/logo.png'))
      ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
      ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
      ->widgets([
        Widgets\AccountWidget::class,
        Widgets\FilamentInfoWidget::class,
      ])
      ->navigationGroups([
        trans('navigations.academics.group'),
        trans('navigations.settings.group'),
        trans('navigations.regions.group'),
      ])
      // ->topNavigation()
      // ->sidebarFullyCollapsibleOnDesktop()
      ->userMenuItems([
        'profile' => MenuItem::make()->label('Edit profile'),
      ])
      ->plugins([
        \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
          ->gridColumns([
            'default' => 1,
            'sm' => 2,
            'lg' => 3
          ])
          ->sectionColumnSpan(1)
          ->checkboxListColumns([
            'default' => 1,
            'sm' => 2,
          ])
          ->resourceCheckboxListColumns([
            'default' => 1,
            'sm' => 2,
          ]),
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
