<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Filament\Resources\VillageResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\District;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\Select::make('district_id')
              ->relationship(name: 'district')
              ->label(trans('pages-districts::page.resource.label.district'))
              ->searchable()
              ->getOptionLabelFromRecordUsing(function (Model $record) {
                return "{$record->name} - {$record->regency->type} {$record->regency->name}, {$record->regency->province->name}";
              })
              ->getSearchResultsUsing(function (string $search): array {
                $keyword = "%$search%";

                return District::query()
                  ->with(['regency.province'])
                  ->where(function (Builder $builder) use ($keyword) {
                    $builder->where('name', 'like', $keyword)
                      ->orWhereHas('regency', function (Builder $subBuilder) use ($keyword) {
                        $subBuilder->where('name', 'like', $keyword)->orWhere('type', 'like', $keyword);
                      });
                  })
                  ->limit(20)
                  ->get()
                  ->mapWithKeys(function (District $district) {
                    return [$district->id => $district->name];
                  })
                  ->toArray();
              })
              ->preload()
              ->columnSpanFull()
              ->required(),
            Forms\Components\Grid::make(3)
              ->schema([
                Forms\Components\TextInput::make('name')
                  ->label(trans('pages-villages::page.field.name'))
                  ->required()
                  ->maxLength(255),
                Forms\Components\TextInput::make('code')
                  ->label(trans('pages-villages::page.field.code'))
                  ->required()
                  ->maxLength(255),
                Forms\Components\TextInput::make('pos_code')
                  ->label(trans('pages-villages::page.field.pos_code'))
                  ->required()
                  ->maxLength(255),
              ])
          ])
          ->columns(1),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultPaginationPageOption(5)
      ->columns([
        Tables\Columns\TextColumn::make('district.regency.province.name')
          ->label(trans('pages-provinces::page.resource.label.province'))
          ->searchable(),
        Tables\Columns\TextColumn::make('district.regency.name')
          ->label(trans('pages-regencies::page.resource.label.regency'))
          ->getStateUsing(
            fn(Village $record) => "{$record->district->regency->type} {$record->district->regency->name}"
          )
          ->searchable(['type', 'name']),
        Tables\Columns\TextColumn::make('district.name')
          ->label(trans('pages-districts::page.resource.label.district'))
          ->sortable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-villages::page.column.name'))
          ->searchable(),
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-villages::page.column.code'))
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('full_code')
          ->label(trans('pages-villages::page.column.full_code'))
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('pos_code')
          ->label(trans('pages-villages::page.column.pos_code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label(trans('pages-villages::page.column.created_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(trans('pages-villages::page.column.updated_at'))
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
            ->mutateFormDataUsing(function (array $data): array {
              if (isset($data['district_id'])) {
                $district = District::findOrFail($data['district_id']);
                $data['full_code'] = $district->full_code . $data['code'];
              }

              return $data;
            })
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.edit.title'),
                body: trans('notification.edit.body', ['label' => trans('pages-villages::page.resource.label.village')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-villages::page.resource.label.village')])
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
      'index' => Pages\ListVillages::route('/'),
      // 'create' => Pages\CreateVillage::route('/create'),
      // 'edit' => Pages\EditVillage::route('/{record}/edit'),
    ];
  }
}
