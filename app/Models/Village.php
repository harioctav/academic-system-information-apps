<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends Model
{
  use HasFactory, Uuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'district_id',
    'name',
    'code',
    'full_code',
    'pos_code',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the district that owns the districts.
   *
   * @return BelongsTo
   */
  public function district(): BelongsTo
  {
    return $this->belongsTo(District::class);
  }

  // /**
  //  * Get the students for the district.
  //  *
  //  * @return HasMany
  //  */
  // public function students(): HasMany
  // {
  //   return $this->hasMany(Student::class);
  // }
}
