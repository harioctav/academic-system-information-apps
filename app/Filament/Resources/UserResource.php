<?php

namespace App\Filament\Resources;

use App\Enums\GeneralConstant;
use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  public static function getNavigationGroup(): ?string
  {
    return trans('navigations.settings.group');
  }

  public static function getNavigationIcon(): string
  {
    return __('pages-users::page.nav.user.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-users::page.nav.user.label');
  }

  public static function getModelLabel(): string
  {
    return __('pages-users::page.resource.label.user');
  }

  public static function getPluralModelLabel(): string
  {
    return __('pages-users::page.resource.label.users');
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
              ->label(trans('pages-users::page.field.name'))
              ->maxLength(50),
            Forms\Components\TextInput::make('email')
              ->label(trans('pages-users::page.field.email'))
              ->email()
              ->unique(ignoreRecord: true)
              ->required()
              ->maxLength(50),
            Forms\Components\TextInput::make('phone')
              ->label(trans('pages-users::page.field.phone'))
              ->nullable()
              ->unique(ignoreRecord: true)
              ->tel()
              ->maxLength(25),
          ])->columns(3),

        Forms\Components\Grid::make(2)
          ->schema([
            Forms\Components\Section::make()
              ->schema([
                Forms\Components\FileUpload::make('avatar')
                  ->label(trans('pages-users::page.field.avatar'))
                  ->avatar()
                  ->image()
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
                  ->required()
                  ->native(false),
                Forms\Components\Select::make('roles')
                  ->label(trans('pages-users::page.field.roles'))
                  ->relationship(name: 'roles', titleAttribute: 'name')
                  ->getOptionLabelFromRecordUsing(
                    fn(Model $record) => UserRole::from($record->name)->label()
                  )
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
      ->recordUrl(null)
      ->checkIfRecordIsSelectableUsing(
        fn(Model $record): bool => $record->roles->implode('name') !== UserRole::SuperAdmin->value
      )
      ->columns([
        Tables\Columns\ImageColumn::make('avatar')
          ->label(trans('pages-users::page.field.avatar'))
          ->circular()
          ->alignCenter()
          ->getStateUsing(
            fn(Model $record) => $record->getUserAvatar()
          ),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-users::page.column.name'))
          ->searchable(),
        Tables\Columns\TextColumn::make('email')
          ->label(trans('pages-users::page.column.email'))
          ->searchable(),
        Tables\Columns\TextColumn::make('phone')
          ->label(trans('pages-users::page.column.phone'))
          ->getStateUsing(fn(Model $record) => $record->phone ?: '-')
          ->searchable(),
        Tables\Columns\TextColumn::make('roles.name')
          ->label(trans('pages-users::page.field.roles'))
          ->getStateUsing(
            fn(Model $record) => UserRole::from($record->roles->implode('name'))->label()
          )
          ->badge()
          ->colors(function (Model $record) {
            $roleColorMap = [
              UserRole::SuperAdmin->value => 'primary',
              UserRole::SubjectRegisTeam->value => 'success',
              UserRole::FinanceTeam->value => 'danger',
              UserRole::StudentRegisTeam->value => 'warning',
              UserRole::FilingTeam->value => 'secondary',
            ];

            return $record->roles->map(function ($role) use ($roleColorMap) {
              return $roleColorMap[$role->name] ?? 'gray';
            })->toArray();
          }),
        Tables\Columns\TextColumn::make('email_verified_at')
          ->label(trans('pages-users::page.column.email_verified_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\IconColumn::make('status')
          ->boolean()
          ->trueIcon('heroicon-o-check-badge')
          ->falseIcon('heroicon-o-x-mark')
          ->getStateUsing(fn(Model $record) => $record->status->value == GeneralConstant::Active->value),
        Tables\Columns\TextColumn::make('created_at')
          ->label(trans('pages-users::page.column.created_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(trans('pages-users::page.column.updated_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make(trans('filament-shield::filament-shield.resource.label.role'))
          ->relationship('roles', 'name')
          ->searchable()
          ->preload()
          ->getOptionLabelFromRecordUsing(
            fn(Model $record) => UserRole::from($record->name)->label()
          )
          ->label(trans('pages-users::page.column.filter.role'))
          ->indicator(trans('filament-shield::filament-shield.resource.label.role')),

        Tables\Filters\SelectFilter::make('status')
          ->options(
            Collection::make(GeneralConstant::cases())->mapWithKeys(fn(GeneralConstant $enum) => [$enum->value => $enum->label()])
          )
          ->label(trans('pages-users::page.column.filter.status'))
          ->indicator('Status')
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
            ->visible(
              fn(Model $record) => $record->roles->implode('name') !== UserRole::SuperAdmin->value
            )
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.edit.title'),
                body: trans('notification.edit.body', ['label' => trans('pages-users::page.resource.label.user')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->hidden(function (Model $record) {
              $isSuperAdmin = $record->roles->contains('name', UserRole::SuperAdmin->value);
              $isActive = $record->status->value !== GeneralConstant::InActive->value;

              return $isSuperAdmin || ($isActive && !$isSuperAdmin);
            })
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-users::page.resource.label.user')])
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

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Section::make(trans('pages-users::page.infolist.title'))
          ->description(trans('pages-users::page.infolist.description', ['name' => $infolist->getRecord()->name]))
          ->icon('heroicon-o-exclamation-circle')
          ->iconColor('info')
          ->schema([
            Grid::make(2)
              ->schema([
                Section::make()
                  ->schema([
                    ImageEntry::make('avatar')
                      ->label(trans('pages-users::page.field.avatar'))
                      ->circular()
                      ->label('Avatar')
                      ->extraAttributes(['class' => 'w-32 h-32'])
                      ->extraAttributes(['class' => 'flex items-center justify-center'])
                      ->getStateUsing(
                        fn(Model $record) => $record->getUserAvatar()
                      ),
                    TextEntry::make('roles.name')
                      ->label(trans('pages-users::page.field.roles'))
                      ->getStateUsing(
                        fn(Model $record) => UserRole::from($record->roles->implode('name'))->label()
                      )
                      ->badge()
                      ->colors(function (Model $record) {
                        $roleColorMap = [
                          UserRole::SuperAdmin->value => 'primary',
                          UserRole::SubjectRegisTeam->value => 'success',
                          UserRole::FinanceTeam->value => 'danger',
                          UserRole::StudentRegisTeam->value => 'warning',
                          UserRole::FilingTeam->value => 'secondary',
                        ];

                        return $record->roles->map(function ($role) use ($roleColorMap) {
                          return $roleColorMap[$role->name] ?? 'gray';
                        })->toArray();
                      }),
                  ])->columnSpan(1),
                Section::make()
                  ->schema([
                    TextEntry::make('name')
                      ->label(trans('pages-users::page.field.name')),
                    TextEntry::make('email')
                      ->label(trans('pages-users::page.field.email')),
                    TextEntry::make('phone')
                      ->label(trans('pages-users::page.field.phone'))
                      ->getStateUsing(fn(Model $record) => $record->phone ?: '-'),
                    TextEntry::make('status')
                      ->badge()
                      ->getStateUsing(
                        fn(Model $record) => $record->status->label()
                      )
                      ->color(
                        fn(Model $record) => $record->status->value == GeneralConstant::Active->value ? 'success' : 'danger'
                      ),
                  ])->columnSpan(1),
              ]),
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
