<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $loader = AliasLoader::getInstance();
    $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
      $switch->locales(['id', 'en']);
    });
  }
}
