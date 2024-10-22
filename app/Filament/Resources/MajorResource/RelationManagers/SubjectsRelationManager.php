<?php

namespace App\Filament\Resources\MajorResource\RelationManagers;

use App\Enums\SubjectSemester;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SubjectsRelationManager extends RelationManager
{
  protected static string $relationship = 'subjects';

  public function form(Form $form): Form
  {
    // dd($this->getMountedTableActionRecord());

    return $form
      ->schema([
        Forms\Components\Select::make('subject_id')
          ->label(trans('pages-subjects::page.nav.subject.label'))
          ->options(function () {
            $record = $this->getMountedTableActionRecord();

            if ($record) {
              return Subject::where('id', $record->subject_id)->pluck('name', 'id');
            }

            return Subject::whereNotIn('id', function ($query) {
              $query->select('subject_id')
                ->from('major_has_subjects')
                ->where('major_id', $this->getOwnerRecord()->id);
            })->pluck('name', 'id');
          })
          ->searchable()
          ->live()
          ->preload()
          ->required()
          ->default(function () {
            $record = $this->getMountedTableActionRecord();
            return $record ? $record->subject_id : null;
          })
          ->disabled(fn() => $this->getMountedTableActionRecord() !== null),

        Forms\Components\Select::make('semester')
          ->options(
            Collection::make(SubjectSemester::cases())->mapWithKeys(fn(SubjectSemester $enum) => [$enum->value => $enum->label()])
          )
          ->enum(SubjectSemester::class)
          ->searchable()
          ->required()
          ->native(false),
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('name')
      ->defaultPaginationPageOption(5)
      ->defaultSort('semester')
      ->columns([
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-majors::page.column.code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-majors::page.column.name'))
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('note')
          ->label(trans('pages-subjects::page.column.note'))
          ->getStateUsing(function (Model $record) {
            return $record->note ?: '-';
          }),
        Tables\Columns\TextColumn::make('semester'),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('semester')
          ->label(trans('pages-majors::page.column.filter.semester'))
          ->indicator(trans('Semester'))
          ->options(
            Collection::make(SubjectSemester::cases())->mapWithKeys(fn(SubjectSemester $enum) => [$enum->value => $enum->label()])
          )
          ->native(false),
      ])
      ->headerActions([
        Tables\Actions\CreateAction::make()
          ->label(trans('button.create', ['label' => trans('pages-subjects::page.nav.subject.label')]))
          ->size(ActionSize::Small)
          ->icon(trans('button.create.icon'))
          ->iconSize('sm')
          ->before(function (array $data) {
            $existingSubjects = DB::table('major_has_subjects')
              ->where('major_id', $this->getOwnerRecord()->id)
              ->whereIn('subject_id', (array) $data['subject_id'])
              ->exists();

            if ($existingSubjects) {
              Notification::make()
                ->danger()
                ->title(trans('pages-majors::page.validation.uniqe'))
                ->send();

              $this->halt();
            }
          })
          ->using(function (array $data, string $model): Model {
            if (is_array($data['subject_id'])) {
              $attachData = collect($data['subject_id'])->mapWithKeys(function ($subjectId) use ($data) {
                return [$subjectId => ['semester' => $data['semester']]];
              })->toArray();

              $this->getOwnerRecord()->subjects()->attach($attachData);
              return Subject::find($data['subject_id'][0]);
            }

            $this->getOwnerRecord()->subjects()->attach($data['subject_id'], [
              'semester' => $data['semester']
            ]);

            return Subject::find($data['subject_id']);
          })
          ->successNotificationTitle(trans('pages-majors::page.validation.success')),
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DetachAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DetachBulkAction::make(),
        ]),
      ]);
  }
}
