<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use App\Helpers\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;

class ListSubjects extends ListRecords
{
  protected static string $resource = SubjectResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->mutateFormDataUsing(function (array $data): array {
          if (isset($data['notes']) && is_array($data['notes'])) {
            $data['note'] = implode(' | ', array_filter($data['notes']));
          } else {
            $data['note'] = null;
          }

          return $data;
        })
        ->successNotification(
          Notification::successNotification(
            title: trans('notification.create.title'),
            body: trans('notification.create.body', ['label' => trans('pages-subjects::page.resource.label.subject')])
          ),
        )
        ->size(ActionSize::Small)
        ->icon(trans('button.create.icon'))
        ->iconSize('sm')
        ->label(trans('button.create', ['label' => trans('pages-subjects::page.resource.label.subject')])),
    ];
  }
}
