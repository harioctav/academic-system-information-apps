<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditUser extends EditRecord
{
  protected static string $resource = UserResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make(),
    ];
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    $oldAvatar = $this->record->avatar ?? null;
    $newAvatar = $data['avatar'] ?? null;

    if ($newAvatar !== null && $newAvatar !== $oldAvatar) {
      if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
        Storage::disk('public')->delete($oldAvatar);
      }
      $data['avatar'] = $newAvatar;
    } elseif ($newAvatar === null && $oldAvatar !== null) {
      if (Storage::disk('public')->exists($oldAvatar)) {
        Storage::disk('public')->delete($oldAvatar);
      }
      $data['avatar'] = null;
    } else {
      $data['avatar'] = $oldAvatar;
    }

    return $data;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
