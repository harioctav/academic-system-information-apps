<?php

namespace App\Models;

use App\Enums\DegreeType;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Major extends Model
{
  use HasFactory, Uuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'code',
    'name',
    'degree',
    'total_course_credit',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'degree' => DegreeType::class
    ];
  }

  /**
   * Get the subjects associated with this major.
   *
   * @return BelongsToMany
   */
  public function subjects(): BelongsToMany
  {
    return $this->belongsToMany(
      Subject::class,
      'major_has_subjects'
    )->using(MajorHasSubject::class)
      ->withPivot('semester');
  }
}
