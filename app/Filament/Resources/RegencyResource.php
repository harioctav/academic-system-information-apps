<?php

namespace App\Filament\Resources;

use App\Enums\RegencyType;
use App\Filament\Resources\RegencyResource\Pages;
use App\Filament\Resources\RegencyResource\RelationManagers;
use App\Helpers\Notification;
use App\Models\Province;
use App\Models\Regency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker as FilterDatePicker;
use Illuminate\Support\Carbon;

class RegencyResource extends Resource
{
  protected static ?string $model = Regency::class;

  public static function getNavigationGroup(): ?string
  {
    return trans('navigations.regions.group');
  }

  public static function getNavigationIcon(): string
  {
    return trans('pages-regencies::page.nav.regency.icon');
  }

  public static function getNavigationLabel(): string
  {
    return trans('pages-regencies::page.nav.regency.label');
  }

  public static function getModelLabel(): string
  {
    return trans('pages-regencies::page.resource.label.regency');
  }

  public static function getPluralModelLabel(): string
  {
    return trans('pages-regencies::page.resource.label.regencies');
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
              ->label(trans('pages-regencies::page.field.code'))
              ->required()
              ->maxLength(5),
            Forms\Components\TextInput::make('name')
              ->label(trans('pages-regencies::page.field.name'))
              ->required()
              ->maxLength(80),
          ])->columns(),
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\Select::make('province_id')
              ->relationship(name: 'province', titleAttribute: 'name')
              ->label(trans('pages-regencies::page.field.province'))
              ->searchable()
              ->preload()
              ->required(),
            Forms\Components\Select::make('type')
              ->options(RegencyType::toSelectArray())
              ->label(trans('pages-regencies::page.field.type'))
              ->preload()
              ->required()
              ->native(false),
          ])->columns(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultPaginationPageOption(5)
      ->columns([
        Tables\Columns\TextColumn::make('province.name')
          ->label(trans('pages-regencies::page.column.province'))
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('code')
          ->label(trans('pages-regencies::page.column.code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('full_code')
          ->label(trans('pages-regencies::page.column.full_code'))
          ->searchable(),
        Tables\Columns\TextColumn::make('type')
          ->label(trans('pages-regencies::page.column.type'))
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(trans('pages-regencies::page.column.name'))
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label(trans('pages-regencies::page.column.created_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label(trans('pages-regencies::page.column.updated_at'))
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('Province')
          ->relationship('province', 'name')
          ->label(trans('pages-regencies::page.column.filter.province'))
          ->searchable()
          ->preload()
          ->indicator(trans('pages-provinces::page.nav.province.label')),

        Tables\Filters\Filter::make('created_at')
          ->form([
            FilterDatePicker::make('created_from')
              ->label(trans('pages-regencies::page.column.filter.created_from')),
            FilterDatePicker::make('created_until')
              ->label(trans('pages-regencies::page.column.filter.created_until')),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['created_from'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
              )
              ->when(
                $data['created_until'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
              );
          })
          ->indicateUsing(function (array $data): array {
            $indicators = [];

            if ($data['created_from'] ?? null) {
              $indicators[] = Tables\Filters\Indicator::make('Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                ->removeField('created_from');
            }

            if ($data['created_until'] ?? null) {
              $indicators[] = Tables\Filters\Indicator::make('Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                ->removeField('created_until');
            }

            return $indicators;
          })->columnSpan(2)->columns(),

      ], layout: Tables\Enums\FiltersLayout::AboveContent)
      ->filtersFormColumns(3)
      ->defaultSort('name')
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
              if (isset($data['province_id'])) {
                $province = Province::findOrFail($data['province_id']);
                $data['full_code'] = $province->code . $data['code'];
              }

              return $data;
            })
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.edit.title'),
                body: trans('notification.edit.body', ['label' => trans('pages-regencies::page.resource.label.regency')])
              ),
            ),
          Tables\Actions\DeleteAction::make()
            ->iconSize('sm')
            ->successNotification(
              Notification::successNotification(
                title: trans('notification.delete.title'),
                body: trans('notification.delete.body', ['label' => trans('pages-regencies::page.resource.label.regency')])
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
      'index' => Pages\ListRegencies::route('/'),
    ];
  }
}
