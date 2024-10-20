<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use App\Models\Regency;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDistrict extends CreateRecord
{
  protected static string $resource = DistrictResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    if (isset($data['regency_id'])) {
      $regency = Regency::findOrFail($data['regency_id']);
      $data['full_code'] = $regency->full_code . $data['code'];
    }

    return $data;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
