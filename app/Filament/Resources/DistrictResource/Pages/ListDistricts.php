<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use App\Helpers\Notification;
use App\Models\Regency;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistricts extends ListRecords
{
  protected static string $resource = DistrictResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->mutateFormDataUsing(function (array $data): array {
          if (isset($data['regency_id'])) {
            $regency = Regency::findOrFail($data['regency_id']);
            $data['full_code'] = $regency->full_code . $data['code'];
          }

          return $data;
        })
        ->successNotification(
          Notification::successNotification(
            title: __('notification.create.title'),
            body: __('notification.create.body', ['label' => __('pages-districts::page.nav.district.label')])
          ),
        ),
    ];
  }
}
