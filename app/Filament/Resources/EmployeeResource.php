<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Carbon\Carbon;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Details')
                    ->description('Basic identification and organizational placement.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('middle_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('suffix')
                            ->maxLength(10)
                            ->placeholder('e.g. Jr, III'),

                        Forms\Components\Select::make('division_id')
                            ->relationship('division', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Personal Information')
                    ->description('Demographics and contact details.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\DatePicker::make('birth_date')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('age', Carbon::parse($state)->age);
                                }
                            }),

                        Forms\Components\TextInput::make('age')
                            ->numeric()
                            ->readOnly()
                            ->extraInputAttributes(['class' => 'bg-gray-50']),

                        Forms\Components\Select::make('gender')
                            ->nullable()
                            ->options([
                                'male'   => 'Male',
                                'female' => 'Female',
                                'other'  => 'Other',
                            ])
                            ->native(false),

                        Forms\Components\TextInput::make('phone')
                            ->label('Contact Number')
                            ->tel()
                            ->mask('09999999999')
                            ->placeholder('09123456789'),

                        Forms\Components\TextInput::make('place_of_birth')
                            ->nullable()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('religion')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('active')
                            ->label('Employment Status')
                            ->helperText('Uncheck to mark as inactive/resigned')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Textarea::make('address')
                            ->nullable()
                            ->columnSpanFull()
                            ->rows(2),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name'])
                    ->weight(FontWeight::Bold)
                    ->description(fn(Employee $record): string => $record->position),

                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->date('M d, Y')
                    ->description(fn(Employee $record): string =>
                        $record->birth_date ? "{$record->age} years old" : '—'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('gender')
                    ->formatStateUsing(fn(?string $state): string =>
                        $state ? ucfirst($state) : '—'
                    )
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('active')
                    ->label('Status')
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Employment Status')
                    ->boolean()
                    ->trueLabel('Active Employees')
                    ->falseLabel('Inactive Employees'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->defaultSort('last_name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit'   => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}