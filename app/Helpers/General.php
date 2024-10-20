<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class General
{
  /**
   * Check if the authenticated user has any of the given permissions.
   *
   * @param  array  $permissions
   * @return bool
   */
  public static function hasAnyPermission(array $permissions): bool
  {
    if (Auth::user()->canAny($permissions)) :
      return true;
    endif;

    return false;
  }
}
