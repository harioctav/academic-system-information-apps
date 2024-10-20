<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Filament\Resources\VillageResource\RelationManagers;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VillageResource extends Resource
{
  protected static ?string $model = Village::class;

  public static function getNavigationIcon(): string
  {
    return trans('pages-villages::page.nav.village.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-villages::page.nav.village.label');
  }

  public static function getModelLabel(): string
  {
    return trans('pages-villages::page.resource.label.village');
  }

  public static function getPluralModelLabel(): string
  {
    return trans('pages-villages::page.resource.label.villages');
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
        Forms\Components\TextInput::make('uuid')
          ->label('UUID')
          ->required()
          ->maxLength(255),
        Forms\Components\Select::make('district_id')
          ->relationship('district', 'name')
          ->required(),
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(255),
        Forms\Components\TextInput::make('code')
          ->required()
          ->maxLength(255),
        Forms\Components\TextInput::make('full_code')
          ->required()
          ->maxLength(255),
        Forms\Components\TextInput::make('pos_code')
          ->required()
          ->maxLength(255),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultPaginationPageOption(5)
      ->columns([
        Tables\Columns\TextColumn::make('uuid')
          ->label('UUID')
          ->searchable(),
        Tables\Columns\TextColumn::make('district.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('code')
          ->searchable(),
        Tables\Columns\TextColumn::make('full_code')
          ->searchable(),
        Tables\Columns\TextColumn::make('pos_code')
          ->searchable(),
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
        //
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
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
      'index' => Pages\ListVillages::route('/'),
      'create' => Pages\CreateVillage::route('/create'),
      'edit' => Pages\EditVillage::route('/{record}/edit'),
    ];
  }
}
