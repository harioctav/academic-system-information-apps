<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum GeneralConstant: int
{
  use EnumsToArray;

  case ACTIVE = 1;
  case INACTIVE = 0;

  public function label(): string
  {
    return match ($this) {
      self::ACTIVE => 'Aktif',
      self::INACTIVE => 'Tidak Aktif',
    };
  }
}