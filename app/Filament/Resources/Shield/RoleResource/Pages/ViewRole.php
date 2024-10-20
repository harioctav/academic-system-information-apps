<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Shield\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewRole extends ViewRecord
{
  protected static string $resource = RoleResource::class;

  protected function getActions(): array
  {
    return [
      Actions\EditAction::make()
        ->hidden(
          fn(Model $record) => $record->name === UserRole::SuperAdmin->value
        ),
    ];
  }
}
