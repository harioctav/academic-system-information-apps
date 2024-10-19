<?php

namespace App\Filament\Resources\RegencyResource\Pages;

use App\Filament\Resources\RegencyResource;
use App\Models\Province;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRegency extends CreateRecord
{
  protected static string $resource = RegencyResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    if (isset($data['province_id'])) {
      $province = Province::findOrFail($data['province_id']);
      $data['full_code'] = $province->code . $data['code'];
    }

    return $data;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
