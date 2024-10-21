<?php

namespace App\Filament\Resources;

use App\Enums\DegreeType;
use App\Filament\Resources\MajorResource\Pages;
use App\Filament\Resources\MajorResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\Major;
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

class MajorResource extends Resource
{
  protected static ?string $model = Major::class;

  public static function getNavigationGroup(): ?string
  {
    return trans('navigations.academics.group');
  }

  public static function getNavigationIcon(): string
  {
    return trans('pages-majors::page.nav.major.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-majors::page.nav.major.label');
  }

  public static function getModelLabel(): string
  {
    return trans('pages-majors::page.resource.label.major');
  }

  public static function getPluralModelLabel(): string
  {
    return trans('pages-majors::page.resource.label.majors');
  }

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function getNavigationBadgeColor(): ?string
  {
    return static::getModel()::count() > 10 ? 'warning' : 'primary';
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\TextInput::make('code')
              ->label(trans('pages-majors::page.field.code'))
              ->required()
              ->unique(ignoreRecord: true)
              ->maxLength(5),
            Forms\Components\TextInput::make('name')
              ->label(trans('pages-majors::page.field.name'))
              ->required()
              ->unique(ignoreRecord: true)
              ->maxLength(80),
            Forms\Components\Select::make('degree')
              ->label(trans('pages-majors::page.field.degree'))
              ->options(
                Collection::make(DegreeType::cases())
                  ->mapWithKeys(
                    fn(DegreeType $enum) => [$enum->value => $enum->label()]
                  )
              )
              ->enum(DegreeType::class)
              ->required()
              ->native(false),
          ])->columns(3),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      // ->headerActions([
      //   Tables\Actions\CreateAction::make()
      //     ->successNotification(
      //       Notification::successNotification(
      //         title: trans('notification.create.title'),
      //         body: trans('notification.create.body', ['label' => trans('pages-majors::page.resource.label.major')])
      //       ),
      //     )
      //     ->size(ActionSize::Small)
      //     ->icon(trans('button.create.icon'))
      //     ->iconSize('sm')
      //     ->label(trans('button.create', ['label' => trans('pages-majors::page.resource.label.major')])),
      // ])
      ->columns([
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-majors::page.column.code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-majors::page.column.name'))
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('degree')
          ->label(trans('pages-majors::page.column.degree'))
          ->getStateUsing(
            fn(Model $record) => DegreeType::from($record->degree->value)->label()
          )
          ->searchable(),
        Tables\Columns\TextColumn::make('total_course_credit')
          ->label(trans('pages-majors::page.column.total_course_credit'))
          ->getStateUsing(
            fn(Model $record) => $record->total_course_credit ?: '-'
          )
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('degree')
          ->label(trans('pages-majors::page.column.filter.degree'))
          ->indicator(trans('pages-majors::page.column.degree'))
          ->options(
            Collection::make(DegreeType::cases())->mapWithKeys(fn(DegreeType $enum) => [$enum->value => $enum->label()])
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
                body: trans('notification.edit.body', ['label' => trans('pages-majors::page.resource.label.major')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-majors::page.resource.label.major')])
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
      'index' => Pages\ListMajors::route('/'),
      'view' => Pages\ViewMajor::route('/{record}'),
    ];
  }
}
