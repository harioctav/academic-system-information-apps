<?php

namespace App\Models;

use App\Enums\DegreeType;
use App\Enums\SubjectNote;
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

  /**
   * Updates the total course credit for the major.
   *
   * This method calculates the total course credit for the major by iterating through the subjects associated with the major. It separates the subjects into two groups: those with "PILIH SALAH SATU" in the note, and those without. It then adds up the course credits for the subjects without "PILIH SALAH SATU", and for the subjects with "PILIH SALAH SATU", it adds the maximum course credit from that group. Finally, it updates the `total_course_credit` column in the `majors` table for this major.
   */
  public function updateTotalCourseCredit()
  {
    $totalCourseCredit = 0;
    $subjects = $this->subjects;
    $subjectsBySemester = $subjects->groupBy('pivot.semester');

    foreach ($subjectsBySemester as $semester => $subjects) {
      // Pisahkan mata kuliah berdasarkan "PILIH SALAH SATU"
      $withPilihSalahSatu = $subjects->filter(function ($subject) {
        return str_contains($subject->note, SubjectNote::PS->value);
      });

      $withoutPilihSalahSatu = $subjects->filter(function ($subject) {
        return !str_contains($subject->note, SubjectNote::PS->value);
      });

      // Tambahkan total SKS dari mata kuliah tanpa "PILIH SALAH SATU"
      foreach ($withoutPilihSalahSatu as $subject) {
        $totalCourseCredit += $subject->course_credit; // Mengambil SKS dari kolom course_credit di tabel subjects
      }

      // Jika ada mata kuliah "PILIH SALAH SATU", hanya tambahkan salah satu dari grup ini
      if ($withPilihSalahSatu->isNotEmpty()) {
        $totalCourseCredit += $withPilihSalahSatu->max()->course_credit;
      }
    }

    // Update nilai total_course_credit pada tabel majors
    $this->update(['total_course_credit' => $totalCourseCredit]);
  }
}
