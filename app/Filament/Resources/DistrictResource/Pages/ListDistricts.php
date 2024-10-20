<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use App\Helpers\Notification;
use App\Models\Regency;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;

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
            title: trans('notification.create.title'),
            body: trans('notification.create.body', ['label' => trans('pages-districts::page.resource.label.district')])
          ),
        )
        ->size(ActionSize::Small)
        ->icon(trans('button.create.icon'))
        ->iconSize('sm')
        ->label(trans('button.create', ['label' => trans('pages-districts::page.resource.label.district')])),
    ];
  }
}
