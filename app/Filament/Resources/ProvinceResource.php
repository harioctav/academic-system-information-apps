<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProvinceResource\Pages;
use App\Filament\Resources\ProvinceResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\Province;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use stdClass;

class ProvinceResource extends Resource
{
  protected static ?string $model = Province::class;

  public static function getNavigationGroup(): ?string
  {
    return trans('navigations.regions.group');
  }

  public static function getNavigationIcon(): string
  {
    return trans('pages-provinces::page.nav.province.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-provinces::page.nav.province.label');
  }

  public static function getModelLabel(): string
  {
    return trans('pages-provinces::page.resource.label.province');
  }

  public static function getPluralModelLabel(): string
  {
    return trans('pages-provinces::page.resource.label.provinces');
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
              ->label(trans('pages-provinces::page.field.code'))
              ->required()
              ->numeric()
              ->maxLength(5),
            Forms\Components\TextInput::make('name')
              ->label(trans('pages-provinces::page.field.name'))
              ->required()
              ->maxLength(80),
          ])->columns(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultPaginationPageOption(5)
      ->columns([
        Tables\Columns\TextColumn::make('No')
          ->alignCenter()
          ->rowIndex(),
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-provinces::page.column.code'))
          ->alignCenter()
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-provinces::page.column.name'))
          ->alignCenter()
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label(trans('pages-provinces::page.column.created_at'))
          ->alignCenter()
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(trans('pages-provinces::page.column.updated_at'))
          ->alignCenter()
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->defaultSort('name')
      ->filters([
        //
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
                body: trans('notification.edit.body', ['label' => trans('pages-provinces::page.resource.label.province')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-provinces::page.resource.label.province')])
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
      'index' => Pages\ListProvinces::route('/'),
    ];
  }
}
