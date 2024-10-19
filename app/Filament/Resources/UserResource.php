<?php

namespace App\Filament\Resources;

use App\Enums\GeneralConstant;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  public static function getNavigationIcon(): string
  {
    return __('pages-users::page.nav.user.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-users::page.nav.user.label');
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
            Forms\Components\TextInput::make('name')
              ->required()
              ->label('Nama Pengguna')
              ->placeholder('Masukkan Nama Pengguna')
              ->maxLength(50),
            Forms\Components\TextInput::make('email')
              ->email()
              ->required()
              ->label('Email')
              ->placeholder('Masukkan Email')
              ->maxLength(50),
            Forms\Components\TextInput::make('phone')
              ->nullable()
              ->tel()
              ->label('Telepon')
              ->placeholder('Masukkan No. Telepon')
              ->maxLength(14),
          ])->columns(3),

        Forms\Components\Grid::make(2)
          ->schema([
            Forms\Components\Section::make()
              ->schema([
                Forms\Components\FileUpload::make('avatar')
                  ->avatar()
                  ->disk('public')
                  ->directory('images/users')
                  ->visibility('public')
                  ->maxSize(2048)
                  ->extraAttributes(['class' => 'flex items-center justify-center']),
              ])->columnSpan(1),
            Forms\Components\Section::make()
              ->schema([
                Forms\Components\Select::make('status')
                  ->options(
                    Collection::make(GeneralConstant::cases())->mapWithKeys(fn(GeneralConstant $enum) => [$enum->value => $enum->label()])
                  )
                  ->enum(GeneralConstant::class)
                  ->native(false),
                Forms\Components\Select::make('roles')
                  ->relationship(name: 'roles', titleAttribute: 'name')
                  ->searchable()
                  ->preload()
                  ->required(),
              ])->columnSpan(1),
          ]),
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
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('email')
          ->searchable(),
        Tables\Columns\TextColumn::make('phone')
          ->searchable(),
        Tables\Columns\TextColumn::make('email_verified_at')
          ->dateTime()
          ->sortable(),
        Tables\Columns\IconColumn::make('status')
          ->boolean(),
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
      'index' => Pages\ListUsers::route('/'),
      'create' => Pages\CreateUser::route('/create'),
      'edit' => Pages\EditUser::route('/{record}/edit'),
    ];
  }
}
