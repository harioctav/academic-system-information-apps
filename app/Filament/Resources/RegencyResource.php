<?php

namespace App\Filament\Resources;

use App\Enums\RegencyType;
use App\Filament\Resources\RegencyResource\Pages;
use App\Filament\Resources\RegencyResource\RelationManagers;
use App\Models\Regency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegencyResource extends Resource
{
  protected static ?string $model = Regency::class;

  protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

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
            Forms\Components\Select::make('province_id')
              ->relationship(name: 'province', titleAttribute: 'name')
              ->searchable()
              ->preload()
              ->required(),
            Forms\Components\Select::make('type')
              ->options(RegencyType::toSelectArray())
              ->preload()
              ->required()
              ->native(false),
          ])->columns(),
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\TextInput::make('code')
              ->required()
              ->maxLength(5),
            Forms\Components\TextInput::make('name')
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
        Tables\Columns\TextColumn::make('province.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('code')
          ->searchable(),
        Tables\Columns\TextColumn::make('full_code')
          ->searchable(),
        Tables\Columns\TextColumn::make('type')
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
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
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->iconSize('sm')
            ->color('info'),
          Tables\Actions\EditAction::make()
            ->color('warning')
            ->icon('heroicon-m-pencil')
            ->iconSize('sm'),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm'),
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
      'index' => Pages\ListRegencies::route('/'),
      'create' => Pages\CreateRegency::route('/create'),
      'edit' => Pages\EditRegency::route('/{record}/edit'),
    ];
  }
}
