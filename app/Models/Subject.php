<?php

namespace App\Models;

use App\Enums\SubjectStatus;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
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
    'course_credit',
    'exam_time',
    'status',
    'note',
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
      'status' => SubjectStatus::class
    ];
  }

  /**
   * Get the majors that are associated with this subject.
   *
   * @return BelongsToMany
   */
  public function majors(): BelongsToMany
  {
    return $this->belongsToMany(
      Major::class,
      'major_has_subjects'
    )
      ->using(MajorHasSubject::class)
      ->withPivot('semester');
  }
}
