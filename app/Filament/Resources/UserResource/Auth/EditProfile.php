<?php

namespace App\Filament\Resources\UserResource\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Storage;

class EditProfile extends BaseEditProfile
{

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

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        FileUpload::make('avatar')
          ->label(trans('pages-users::page.field.avatar'))
          ->avatar()
          ->image()
          ->disk('public')
          ->directory('images/users')
          ->visibility('public')
          ->maxSize(2048)
          ->extraAttributes(['class' => 'flex items-center justify-center']),
        $this->getNameFormComponent(),
        $this->getEmailFormComponent(),
        $this->getPasswordFormComponent(),
        $this->getPasswordConfirmationFormComponent(),
      ]);
  }
}
