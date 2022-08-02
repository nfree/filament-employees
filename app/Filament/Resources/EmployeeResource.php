<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use App\Models\Country;
use App\Models\Employee;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeeStateOverview;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Select::make('country_id')
                        ->label('Country')
                        ->options(Country::all()->pluck('name', 'id')->toArray())
                        ->afterStateUpdated(fn (callable $set) => $set('state_id', null))
                        ->reactive(),
                    Select::make('state_id')
                        ->options(function (callable $get) {
                            $country = Country::find($get('country_id'));
                            if (!$country) {
                                return Country::all()->pluck('name', 'id')->toArray();
                            }
                            return $country->states->pluck('name', 'id')->toArray();
                        })
                        ->afterStateUpdated(fn (callable $set) => $set('city_id', null))
                        ->reactive(),
                    Select::make('city_id')
                        ->options(function (callable $get) {
                            $states = State::find($get('state_id'));
                            if (!$states) {
                                return State::all()->pluck('name', 'id')->toArray();
                            }
                            return $states->cities->pluck('name', 'id')->toArray();
                        })
                        ->reactive(),
                    Select::make('department_id')->relationship('department', 'name')
                        ->required(),
                ]),

                Card::make()->schema([
                    TextInput::make('first_name'),
                    TextInput::make('last_name'),
                    TextInput::make('address'),
                    TextInput::make('zip_code'),
                    DatePicker::make('birth_date'),
                    DatePicker::make('date_hired'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('country.name')->sortable()->searchable(),
                TextColumn::make('department.name')->sortable()->searchable(),
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('date_hired'),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
                //
            ])
            ->filters([
                SelectFilter::make('department_id')->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeeStateOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
