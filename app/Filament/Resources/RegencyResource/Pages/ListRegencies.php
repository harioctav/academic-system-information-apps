<?php

namespace App\Filament\Resources\RegencyResource\Pages;

use App\Filament\Resources\RegencyResource;
use App\Helpers\Notification;
use App\Models\Province;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegencies extends ListRecords
{
  protected static string $resource = RegencyResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->mutateFormDataUsing(function (array $data): array {
          if (isset($data['province_id'])) {
            $province = Province::findOrFail($data['province_id']);
            $data['full_code'] = $province->code . $data['code'];
          }

          return $data;
        })
        ->successNotification(
          Notification::successNotification(
            title: __('notification.create.title'),
            body: __('notification.create.body', ['label' => __('pages-regencies::page.nav.regency.label')])
          ),
        ),
    ];
  }
}
