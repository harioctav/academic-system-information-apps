<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Province extends Model
{
  use HasFactory, Uuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'name',
    'code',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the regencies for the province.
   *
   * @return HasMany
   */
  public function regencies(): HasMany
  {
    return $this->hasMany(Regency::class, 'province_id');
  }

  /**
   * Get the districts for the province.
   *
   * @return HasManyThrough
   */
  public function districts(): HasManyThrough
  {
    return $this->hasManyThrough(District::class, Regency::class);
  }
}
