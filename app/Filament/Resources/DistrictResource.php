<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Filament\Resources\DistrictResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\District;
use App\Models\Regency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistrictResource extends Resource
{
  protected static ?string $model = District::class;

  public static function getNavigationGroup(): ?string
  {
    return trans('navigations.regions.group');
  }

  public static function getNavigationIcon(): string
  {
    return trans('pages-districts::page.nav.district.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-districts::page.nav.district.label');
  }

  public static function getModelLabel(): string
  {
    return trans('pages-districts::page.resource.label.district');
  }

  public static function getPluralModelLabel(): string
  {
    return trans('pages-districts::page.resource.label.districts');
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
        Forms\Components\Select::make('regency_id')
          ->relationship(
            name: 'regency',
            // titleAttribute: 'name'
          )
          ->label(trans('pages-districts::page.field.regency'))
          ->searchable()
          ->getOptionLabelFromRecordUsing(
            fn(Model $record) => "{$record->type} {$record->name}"
          )
          ->preload()
          ->required(),
        Forms\Components\TextInput::make('code')
          ->label(trans('pages-districts::page.field.code'))
          ->required()
          ->maxLength(5),
        Forms\Components\TextInput::make('name')
          ->label(trans('pages-districts::page.field.name'))
          ->required()
          ->maxLength(50),
      ])->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultPaginationPageOption(5)
      ->columns([
        Tables\Columns\TextColumn::make('regency.province.name')
          ->label(trans('pages-provinces::page.resource.label.province'))
          ->searchable(),
        Tables\Columns\TextColumn::make('regency.name')
          ->label(trans('pages-districts::page.column.regency'))
          ->getStateUsing(
            fn(District $record) => "{$record->regency->type} {$record->regency->name}"
          )
          ->sortable(),
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-districts::page.column.code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-districts::page.column.name'))
          ->searchable(),
        Tables\Columns\TextColumn::make('full_code')
          ->label(trans('pages-districts::page.column.full_code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label(trans('pages-districts::page.column.created_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(trans('pages-districts::page.column.updated_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->defaultSort('name')
      ->filters([
        Tables\Filters\SelectFilter::make('Regency')
          ->relationship('regency', 'name')
          ->label(trans('pages-districts::page.column.filter.regency'))
          ->searchable()
          ->getOptionLabelFromRecordUsing(
            fn(Regency $record) => "{$record->type} {$record->name}"
          )
          ->preload()
          ->indicator(trans('pages-regencies::page.nav.regency.label')),
      ], layout: Tables\Enums\FiltersLayout::AboveContent)
      ->filtersFormColumns(2)
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->iconSize('sm')
            ->color('info'),
          Tables\Actions\EditAction::make()
            ->color('warning')
            ->icon('heroicon-m-pencil')
            ->iconSize('sm')
            ->mutateFormDataUsing(function (array $data): array {
              if (isset($data['regency_id'])) {
                $regency = Regency::findOrFail($data['regency_id']);
                $data['full_code'] = $regency->full_code . $data['code'];
              }

              return $data;
            })
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.edit.title'),
                body: trans('notification.edit.body', ['label' => trans('pages-districts::page.resource.label.district')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-districts::page.resource.label.district')])
              ),
            ),
        ])
          ->button()
          ->size('sm')
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
      'index' => Pages\ListDistricts::route('/'),
    ];
  }
}
