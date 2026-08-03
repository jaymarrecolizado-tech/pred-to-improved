<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-m-users';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Admin Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('User Details')
                            ->description('Manage user profile information and system access.')
                            ->icon('heroicon-m-user-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->suffix('@dict.gov.ph')
                                    ->helperText('Must be a valid @dict.gov.ph email address.')
                                    ->rules(['ends_with:@dict.gov.ph']),

                                Forms\Components\Select::make('role')
                                    ->options([
                                        'employee'    => 'Employee',
                                        'admin'       => 'Admin',
                                        'hr'          => 'HR Officer',
                                        'super_admin' => 'Super Admin',
                                    ])
                                    ->native(false)
                                    ->required(),
                            ])
                            ->columnSpan(2),

                        Section::make('Security & Signature')
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn($state) => filled($state))
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->minLength(12)
                                    ->rules(fn(string $context) => $context === 'create' || request()->filled('data.password')
                                        ? ['min:12', 'regex:/[A-Z]/', 'regex:/[0-9]/']
                                        : []
                                    )
                                    ->helperText('Minimum 12 characters, at least one uppercase letter and one number.')
                                    ->hint('Min. 12 characters'),

                                Forms\Components\FileUpload::make('signature')
                                    ->directory('signatures')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(5120),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),

                Tables\Columns\TextColumn::make('name')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin'       => 'success',
                        'hr'          => 'warning',
                        'employee'    => 'info',
                        default       => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'super_admin' => 'heroicon-m-star',
                        'admin'       => 'heroicon-m-shield-check',
                        'hr'          => 'heroicon-m-identification',
                        'employee'    => 'heroicon-m-user',
                        default       => 'heroicon-m-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'employee'    => 'Employee',
                        'admin'       => 'Admin',
                        'hr'          => 'HR Officer',
                        'super_admin' => 'Super Admin',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Users Found')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}