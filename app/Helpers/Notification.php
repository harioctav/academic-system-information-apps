<?php

namespace App\Helpers;

use Filament\Notifications\Notification as FilamentNotification;

class Notification
{
  public static function successNotification(string $title, string $body): FilamentNotification
  {
    return FilamentNotification::make()
      ->success()
      ->title($title)
      ->body($body);
  }
}
