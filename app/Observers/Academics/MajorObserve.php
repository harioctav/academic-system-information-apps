<?php

namespace App\Observers\Academics;

use App\Filament\Resources\MajorResource;
use App\Helpers\Notification;
use App\Models\Major;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MajorObserve
{
  /**
   * Handle the Major "created" event.
   */
  public function created(Major $major): void
  {
    $recepient = User::active()->isAdmin()->get();
    $user = Auth::user();

    Notification::databaseNotification(
      recepient: $recepient,
      title: trans('notification.create.title'),
      body: trans('notification.database.create.body', [
        'user' => $user->name,
        'label' => trans('pages-majors::page.resource.label.major'),
        'name' => $major->name
      ]),
      icon: 'heroicon-o-plus',
      url: MajorResource::getUrl('view', ['record' => $major])
    );
  }

  /**
   * Handle the Major "updated" event.
   */
  public function updated(Major $major): void
  {
    $recepient = User::active()->isAdmin()->get();
    $user = Auth::user();

    Notification::databaseNotification(
      recepient: $recepient,
      title: trans('notification.edit.title'),
      body: trans('notification.database.edit.body', [
        'user' => $user->name,
        'label' => trans('pages-majors::page.resource.label.major'),
        'name' => $major->name
      ]),
      icon: 'heroicon-o-pencil',
      url: MajorResource::getUrl('edit', ['record' => $major])
    );
  }

  /**
   * Handle the Major "deleted" event.
   */
  public function deleted(Major $major): void
  {
    $recepient = User::active()->isAdmin()->get();
    $user = Auth::user();

    Notification::databaseNotification(
      recepient: $recepient,
      title: trans('notification.delete.title'),
      body: trans('notification.database.delete.body', [
        'user' => $user->name,
        'label' => trans('pages-majors::page.resource.label.major'),
        'name' => $major->name
      ]),
      icon: 'heroicon-o-trash',
      url: ''
    );
  }

  /**
   * Handle the Major "restored" event.
   */
  public function restored(Major $major): void
  {
    //
  }

  /**
   * Handle the Major "force deleted" event.
   */
  public function forceDeleted(Major $major): void
  {
    //
  }
}
