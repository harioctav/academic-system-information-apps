<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum RegencyType: string
{
  use EnumsToArray;

  case KABUPATEN = 'Kabupaten';
  case KOTA = 'Kota';
}
