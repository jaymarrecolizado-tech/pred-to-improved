<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelSourceResource\Pages;
use App\Models\TravelSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight; // Required for bold text

class TravelSourceResource extends Resource
{
    protected static ?string $model = TravelSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Travel Source Details')
                    ->description('Manage funding types and associated vehicles.')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Fund Type')
                            ->placeholder('e.g., General Fund, Project Based')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),

                        Forms\Components\Repeater::make('vehicles')
                            ->label('Assigned Vehicles')
                            ->schema([
                                Forms\Components\TextInput::make('car_name')
                                    ->label('Car Name')
                                    ->placeholder('Toyota Hi-Ace')
                                    ->required(),

                                Forms\Components\TextInput::make('plate_number')
                                    ->label('Plate Number')
                                    ->placeholder('ABC-1234')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel('Add New Vehicle')
                            ->reorderable()
                            ->collapsible() // Good for long lists
                            ->itemLabel(fn (array $state): ?string => $state['car_name'] ?? null),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Fund Type')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold) // Bold text for hierarchy
                    ->color('primary'),

                Tables\Columns\TextColumn::make('vehicles')
                    ->label('Registered Vehicles')
                    ->badge() // Display vehicles as badges
                    ->formatStateUsing(fn ($state) => count($state) . ' Vehicles')
                    ->description(fn (TravelSource $record): string =>
                        collect($record->vehicles)->pluck('car_name')->implode(', ')
                    )
                    ->color('info')
                    ->icon('heroicon-m-truck'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since() // Human-friendly time
                    ->dateTimeTooltip()
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('No Travel Sources Found')
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTravelSources::route('/'),
            'create' => Pages\CreateTravelSource::route('/create'),
            'edit' => Pages\EditTravelSource::route('/{record}/edit'),
        ];
    }
}
