<?php

namespace App\Enums;

use App\Traits\EnumsToArray;

enum UserRole: string
{
  use EnumsToArray;

  case ADMIN = 'super_admin';
  case REGIS_TEAM = 'registration_team';
  case FINANCE_TEAM = 'finance_team';
  case PPDB_TEAM = 'ppdb_team';
  case FILING_TEAM = 'filing_team';

  public function label(): string
  {
    return match ($this) {
      self::ADMIN => 'Super Admin',
      self::REGIS_TEAM => 'Tim Regis Matakuliah',
      self::FINANCE_TEAM => 'Tim Keuangan',
      self::PPDB_TEAM => 'Tim PPDB Maba',
      self::FILING_TEAM => 'Tim Pemberkasan',
    };
  }
}
