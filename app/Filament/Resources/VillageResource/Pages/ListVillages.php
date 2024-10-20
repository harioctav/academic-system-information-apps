<?php

namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use App\Helpers\Notification;
use App\Models\District;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;

class ListVillages extends ListRecords
{
  protected static string $resource = VillageResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->mutateFormDataUsing(function (array $data): array {

          if (isset($data['district_id'])) {
            $district = District::findOrFail($data['district_id']);
            $data['full_code'] = $district->full_code . $data['code'];
          }

          return $data;
        })
        ->successNotification(
          Notification::successNotification(
            title: trans('notification.create.title'),
            body: trans('notification.create.body', ['label' => trans('pages-villages::page.resource.label.village')])
          ),
        )
        ->size(ActionSize::Small)
        ->icon(trans('button.create.icon'))
        ->iconSize('sm')
        ->label(trans('button.create', ['label' => trans('pages-villages::page.resource.label.village')])),
    ];
  }
}
