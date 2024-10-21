<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum SubjectStatus: string
{
  use EnumsToArray;

  case INTI = 'I';
  case NON_INTI = 'N';

  /**
   * Get human-readable label for the enum value
   */
  public function label(): string
  {
    return match ($this) {
      self::INTI => 'Inti',
      self::NON_INTI => 'Non Inti',
    };
  }
}
