<?php

namespace App\Imports\Academics;

use App\Models\Major;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class MajorSubjectImport implements ToCollection, WithHeadingRow
{
  protected $errors = [];
  protected $imported = 0;
  protected $skipped = 0;
  protected $feedbackMessage = '';

  /**
   * @param Collection $collection
   */
  public function collection(Collection $collection)
  {
    try {
      DB::beginTransaction();

      // Temporarily disable observer for bulk operations
      Major::unsetEventDispatcher();

      $existingMajors = Major::pluck('id', 'code')->toArray();
      $existingSubjects = Subject::pluck('id', 'code')->toArray();

      $majorsToUpdate = [];
      $majorsToInsert = [];
      $subjectsToUpsert = [];
      $majorSubjectRelations = [];

      $uniqueMajors = [];
      $uniqueSubjects = [];

      // Counter variables for feedback
      $newMajorsCount = 0;
      $updatedMajorsCount = 0;
      $newSubjectsCount = 0;
      $updatedSubjectsCount = 0;
      $newRelationsCount = 0;
      $updatedRelationsCount = 0;

      foreach ($collection as $row) {
        $majorCode = trim($row['kode_jurusan'] ?? '');
        $majorName = trim($row['jurusan'] ?? '') ?: null;
        $majorDegree = trim($row['jenjang_jurusan'] ?? '') ?: null;
        $subjectCode = trim($row['kode'] ?? '');
        $subjectName = trim($row['matakuliah'] ?? '') ?: null;
        $semester = trim($row['semester'] ?? '');
        $courseCredit = trim($row['sks'] ?? '') ?: 0;
        $subjectStatus = trim($row['status_matakuliah'] ?? '');
        $examTime = trim($row['waktu_ujian'] ?? '');
        $subjectNote = trim($row['keterangan'] ?? '') ?: null;

        // Validate required fields
        if (empty($majorCode) || empty($subjectCode)) {
          $this->errors[] = "Baris dilewati: Kode jurusan atau kode mata kuliah kosong.";
          continue;
        }

        // Handle Major
        if (!isset($uniqueMajors[$majorCode])) {
          $majorData = [
            'uuid' => Str::uuid(),
            'code' => $majorCode,
            'name' => $majorName,
            'degree' => $majorDegree,
            'created_at' => now(),
            'updated_at' => now()
          ];
          if (isset($existingMajors[$majorCode])) {
            $majorsToUpdate[$existingMajors[$majorCode]] = $majorData;
            $updatedMajorsCount++;
          } else {
            $majorsToInsert[] = $majorData;
            $newMajorsCount++;
          }
          $uniqueMajors[$majorCode] = true;
        }

        // Handle subject
        if (!isset($uniqueSubjects[$subjectCode])) {
          $subjectData = [
            'code' => $subjectCode,
            'name' => $subjectName,
            'course_credit' => $courseCredit,
            'status' => $subjectStatus,
            'exam_time' => $examTime,
            'note' => $subjectNote,
            'updated_at' => now()
          ];

          if (!isset($existingSubjects[$subjectCode])) {
            $subjectData['uuid'] = Str::uuid();
            $subjectData['created_at'] = now();
            $newSubjectsCount++;
          } else {
            $updatedSubjectsCount++;
          }

          $subjectsToUpsert[] = $subjectData;
          $uniqueSubjects[$subjectCode] = true;
        }

        // Prepare Major-Subject Relation
        $majorSubjectRelations[] = [
          'major_code' => $majorCode,
          'subject_code' => $subjectCode,
          'semester' => $semester,
        ];
      }

      // Bulk update and insert Majors
      foreach ($majorsToUpdate as $id => $data) {
        try {
          Major::where('id', $id)->update($data);
        } catch (\Exception $e) {
          $this->errors[] = "Error updating major ID {$id}: " . $e->getMessage();
        }
      }

      if (!empty($majorsToInsert)) {
        try {
          Major::insert($majorsToInsert);
        } catch (\Exception $e) {
          $this->errors[] = "Error inserting new majors: " . $e->getMessage();
        }
      }

      // Bulk upsert Subjects
      foreach ($subjectsToUpsert as $subject) {
        try {
          Subject::updateOrInsert(
            ['code' => $subject['code']],
            $subject
          );
        } catch (\Exception $e) {
          $this->errors[] = "Error upserting subject {$subject['code']}: " . $e->getMessage();
        }
      }

      // Refresh majors and subjects after bulk operations
      $majors = Major::pluck('id', 'code')->toArray();
      $subjects = Subject::pluck('id', 'code')->toArray();

      // Bulk insert or update Major-Subject relations
      foreach ($majorSubjectRelations as $relation) {
        $majorId = $majors[$relation['major_code']] ?? null;
        $subjectId = $subjects[$relation['subject_code']] ?? null;

        if (!$majorId || !$subjectId) {
          $this->errors[] = "Skipping relation: Major {$relation['major_code']} or Subject {$relation['subject_code']} not found.";
          continue;
        }

        try {
          $updated = DB::table('major_has_subjects')->updateOrInsert(
            [
              'major_id' => $majorId,
              'subject_id' => $subjectId
            ],
            [
              'uuid' => Str::uuid(),
              'semester' => $relation['semester'],
              'created_at' => now(),
              'updated_at' => now()
            ]
          );

          if ($updated) {
            $updatedRelationsCount++;
          } else {
            $newRelationsCount++;
          }
        } catch (\Exception $e) {
          $this->errors[] = "Error processing relation for major {$relation['major_code']}: " . $e->getMessage();
        }
      }

      // Update total credits untuk semua major yang terpengaruh
      $processedMajorIds = array_unique(array_values($majors));
      foreach ($processedMajorIds as $majorId) {
        try {
          $major = Major::find($majorId);
          if ($major) {
            $major->updateTotalCourseCredit(true);
          }
        } catch (\Exception $e) {
          $this->errors[] = "Error updating total credit for major ID {$majorId}: " . $e->getMessage();
        }
      }

      $this->imported = $newMajorsCount + $newSubjectsCount + $newRelationsCount;
      $this->skipped = $updatedMajorsCount + $updatedSubjectsCount;

      DB::commit();

      // Prepare feedback message
      $this->feedbackMessage = "Import selesai:\n";
      $this->feedbackMessage .= "- Jurusan baru: {$newMajorsCount}\n";
      $this->feedbackMessage .= "- Jurusan diperbarui: {$updatedMajorsCount}\n";
      $this->feedbackMessage .= "- Mata kuliah baru: {$newSubjectsCount}\n";
      $this->feedbackMessage .= "- Mata kuliah diperbarui: {$updatedSubjectsCount}\n";
      $this->feedbackMessage .= "- Relasi jurusan-mata kuliah baru: {$newRelationsCount}\n";
      $this->feedbackMessage .= "- Relasi jurusan-mata kuliah diperbarui: {$updatedRelationsCount}\n";

      if (!empty($this->errors)) {
        $this->feedbackMessage .= "\nPeringatan:\n" . implode("\n", $this->errors);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error importing: ' . $e->getMessage());
      $this->errors[] = 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage();
      $this->feedbackMessage = 'Terjadi kesalahan saat mengimpor data. Silakan periksa log untuk detail lebih lanjut.';
    } finally {
      // Make sure observer is re-enabled even if there's an error
      Major::setEventDispatcher(app()->make('events'));
    }
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function getImportedCount()
  {
    return $this->imported;
  }

  public function getSkippedCount()
  {
    return $this->skipped;
  }

  public function getFeedbackMessage()
  {
    return $this->feedbackMessage;
  }
}
