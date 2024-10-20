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

class ProvinceResource extends Resource
{
  protected static ?string $model = Province::class;

  public static function getNavigationIcon(): string
  {
    return __('pages-provinces::page.nav.province.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-provinces::page.nav.province.label');
  }

  public static function getModelLabel(): string
  {
    return __('pages-provinces::page.resource.label.province');
  }

  public static function getPluralModelLabel(): string
  {
    return __('pages-provinces::page.resource.label.provinces');
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
              ->label(__('pages-provinces::page.field.code'))
              ->required()
              ->numeric()
              ->maxLength(5),
            Forms\Components\TextInput::make('name')
              ->label(__('pages-provinces::page.field.name'))
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
        Tables\Columns\TextColumn::make('code')
          ->label(__('pages-provinces::page.column.code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(__('pages-provinces::page.column.name'))
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label(__('pages-provinces::page.column.created_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(__('pages-provinces::page.column.updated_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
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
                title: __('notification.edit.title'),
                body: __('notification.edit.body', ['label' => __('pages-provinces::page.nav.province.label')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: __('notification.delete.title'),
                body: __('notification.delete.body', ['label' => __('pages-provinces::page.nav.province.label')])
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
