<?php

namespace App\Models;

use App\Enums\DegreeType;
use App\Enums\SubjectNote;
use App\Observers\Academics\MajorObserve;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[ObservedBy(MajorObserve::class)]
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

  /**
   * Updates the total course credit for the major.
   *
   * This method calculates the total course credit for the major by iterating through the subjects associated with the major. It separates the subjects into two groups: those with "PILIH SALAH SATU" in the note, and those without. It then adds up the course credits for the subjects without "PILIH SALAH SATU", and for the subjects with "PILIH SALAH SATU", it adds the maximum course credit from that group. Finally, it updates the `total_course_credit` column in the `majors` table for this major.
   */
  public function updateTotalCourseCredit($skipObserver = false)
  {
    if ($skipObserver) {
      // Temporarily disable observer
      self::unsetEventDispatcher();
    }

    try {
      $totalCourseCredit = 0;
      // Eager load subjects untuk menghindari N+1 query
      $this->load('subjects');
      $subjects = $this->subjects;
      $subjectsBySemester = $subjects->groupBy('pivot.semester');

      foreach ($subjectsBySemester as $semester => $semesterSubjects) {
        // Pisahkan mata kuliah berdasarkan "PILIH SALAH SATU"
        $withPilihSalahSatu = $semesterSubjects->filter(function ($subject) {
          return str_contains($subject->note, SubjectNote::PS->value);
        });

        $withoutPilihSalahSatu = $semesterSubjects->filter(function ($subject) {
          return !str_contains($subject->note, SubjectNote::PS->value);
        });

        // Tambahkan total SKS dari mata kuliah tanpa "PILIH SALAH SATU"
        foreach ($withoutPilihSalahSatu as $subject) {
          $totalCourseCredit += $subject->course_credit;
        }

        // Jika ada mata kuliah "PILIH SALAH SATU", hanya tambahkan yang terbesar
        if ($withPilihSalahSatu->isNotEmpty()) {
          $maxCreditSubject = $withPilihSalahSatu->max(function ($subject) {
            return $subject->course_credit;
          });
          $totalCourseCredit += $maxCreditSubject;
        }
      }

      // Update dengan menghindari recursive observer calls
      DB::table('majors')
        ->where('id', $this->id)
        ->update(['total_course_credit' => $totalCourseCredit]);

      // Refresh model untuk memastikan data terupdate
      $this->refresh();
    } catch (\Exception $e) {
      Log::error("Error updating total course credit for major {$this->code}: " . $e->getMessage());
      throw $e;
    } finally {
      if ($skipObserver) {
        // Re-enable observer
        self::setEventDispatcher(app()->make('events'));
      }
    }
  }
}
