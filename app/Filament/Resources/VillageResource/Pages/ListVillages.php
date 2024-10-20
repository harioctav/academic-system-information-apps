<?php

namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use App\Helpers\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVillages extends ListRecords
{
  protected static string $resource = VillageResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->successNotification(
          Notification::successNotification(
            title: trans('notification.create.title'),
            body: trans('notification.create.body', ['label' => trans('pages-villages::page.resource.label.village')])
          ),
        )
        ->icon(trans('button.create.icon'))
        ->iconSize('sm')
        ->label(trans('button.create', ['label' => trans('pages-villages::page.resource.label.village')])),
    ];
  }
}
