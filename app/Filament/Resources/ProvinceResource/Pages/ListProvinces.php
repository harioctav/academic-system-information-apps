<?php

namespace App\Filament\Resources\ProvinceResource\Pages;

use App\Filament\Resources\ProvinceResource;
use App\Helpers\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProvinces extends ListRecords
{
  protected static string $resource = ProvinceResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->successNotification(
          Notification::successNotification(
            title: __('notification.create.title'),
            body: __('notification.create.body', ['label' => __('pages-provinces::page.nav.province.label')])
          ),
        ),
    ];
  }
}
