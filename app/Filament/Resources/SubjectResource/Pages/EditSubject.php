<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\ActionSize;

class EditSubject extends EditRecord
{
  protected static string $resource = SubjectResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Action::make('back')
        ->label(trans('button.back'))
        ->url(static::getResource()::getUrl())
        ->button()
        ->size(ActionSize::Small)
        ->icon(trans('button.back.icon'))
        ->iconSize('sm')
        ->color('secondary'),
    ];
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    if (isset($data['notes']) && is_array($data['notes'])) {
      $data['note'] = implode(' | ', array_filter($data['notes']));
    } else {
      $data['note'] = null;
    }

    return $data;
  }

  protected function getSavedNotification(): ?Notification
  {
    return Notification::make()
      ->success()
      ->title(trans('notification.edit.title'))
      ->body(trans('notification.edit.body', ['label' => trans('pages-subjects::page.resource.label.subject')]));
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
