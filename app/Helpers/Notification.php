<?php

namespace App\Helpers;

use Filament\Notifications\Actions\Action;
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

  public static function databaseNotification(
    $recepient,
    $url = null,
    string $title = 'New Notification',
    string $icon = 'heroicon-o-check',
    string $body
  ): FilamentNotification {
    return FilamentNotification::make()
      ->title($title)
      ->icon($icon)
      ->body($body)
      ->actions([
        Action::make('View')
          ->url($url)
      ])
      ->sendToDatabase($recepient);
  }
}
