<?php

namespace App\Filament\Resources;

use App\Enums\SubjectNote;
use App\Enums\SubjectStatus;
use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
  protected static ?string $model = Subject::class;

  public static function getNavigationGroup(): ?string
  {
    return trans('navigations.academics.group');
  }

  public static function getNavigationIcon(): string
  {
    return trans('pages-subjects::page.nav.subject.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-subjects::page.nav.subject.label');
  }

  public static function getModelLabel(): string
  {
    return trans('pages-subjects::page.resource.label.subject');
  }

  public static function getPluralModelLabel(): string
  {
    return trans('pages-subjects::page.resource.label.subjects');
  }

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function getNavigationBadgeColor(): ?string
  {
    return static::getModel()::count() > 100 ? 'warning' : 'primary';
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\TextInput::make('code')
              ->label(trans('pages-subjects::page.field.code'))
              ->string()
              ->required()
              ->unique(ignoreRecord: true)
              ->maxLength(20),
            Forms\Components\TextInput::make('name')
              ->label(trans('pages-subjects::page.field.name'))
              ->string()
              ->required()
              ->unique(ignoreRecord: true)
              ->maxLength(80),
            Forms\Components\TextInput::make('course_credit')
              ->label(trans('pages-subjects::page.field.course_credit'))
              ->required()
              ->numeric()
              ->rule('between:2,5')
              ->step(1)
              ->minValue(2)
              ->maxValue(5)
              ->maxLength(5),
          ])->columns(3),
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\TextInput::make('exam_time')
              ->label(trans('pages-subjects::page.field.exam_time'))
              ->numeric()
              ->required()
              ->minValue(1)
              ->regex('/^\d+([.,]\d+)?$/'),
            Forms\Components\Select::make('status')
              ->options(
                Collection::make(SubjectStatus::cases())->mapWithKeys(fn(SubjectStatus $enum) => [$enum->value => $enum->label()])
              )
              ->enum(SubjectStatus::class)
              ->required()
              ->native(false),
          ])->columns(2),

        Forms\Components\Section::make()
          ->schema([
            Forms\Components\CheckboxList::make('notes')
              ->options(SubjectNote::toSelectArray())
              ->columns(4)
              ->gridDirection('row')
              ->bulkToggleable()
              ->afterStateHydrated(function (Forms\Components\CheckboxList $component, $state, ?Model $record) {
                if ($record && !empty($record->note)) {
                  $selectedNotes = explode(' | ', $record->note);
                  $component->state(
                    collect(SubjectNote::toSelectArray())
                      ->filter(fn($value, $key) => in_array($value, $selectedNotes))
                      ->keys()
                      ->toArray()
                  );
                } else {
                  $component->state([]);
                }
              }),
          ]),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultPaginationPageOption(5)
      ->columns([
        Tables\Columns\TextColumn::make('No')
          ->rowIndex(),
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-subjects::page.column.code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-subjects::page.column.name'))
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('status')
          ->getStateUsing(
            fn(Model $record) => SubjectStatus::from($record->status->value)->label()
          )
          ->searchable(),
        Tables\Columns\TextColumn::make('exam_time')
          ->label(trans('pages-subjects::page.column.exam_time'))
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label(trans('pages-subjects::page.column.created_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(trans('pages-subjects::page.column.updated_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('status')
          ->label(trans('pages-subjects::page.column.filter.status'))
          ->indicator(trans('Status'))
          ->options(
            Collection::make(SubjectStatus::cases())->mapWithKeys(fn(SubjectStatus $enum) => [$enum->value => $enum->label()])
          )
          ->native(false),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->iconSize('sm')
            ->color('info'),
          Tables\Actions\EditAction::make()
            ->color('warning')
            ->icon('heroicon-m-pencil')
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.edit.title'),
                body: trans('notification.edit.body', ['label' => trans('pages-subjects::page.resource.label.subject')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-subjects::page.resource.label.subject')])
              ),
            ),
        ])
          ->button()
          ->size(ActionSize::Small)
          ->icon('heroicon-m-ellipsis-vertical'),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListSubjects::route('/'),
      'edit' => Pages\EditSubject::route('/{record}/edit'),
    ];
  }
}
