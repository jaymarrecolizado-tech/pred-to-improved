<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserGuideResource\Pages;
use App\Models\UserGuide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserGuideResource extends Resource
{
    protected static ?string $model = UserGuide::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'User Guide';
    protected static ?string $modelLabel = 'Guide Step';
    protected static ?string $pluralModelLabel = 'User Guide';
    protected static ?string $slug = 'user-guide';
    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->orderBy('sort_order')->orderBy('title');

        if (!auth()->user()?->isSuperAdmin()) {
            $query->published();
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Guide step')
                    ->description('Use a step number and ordered sub-steps so staff can follow the flow in sequence.')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Step number')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Shown as Step 1, Step 2, and so on.'),

                        Forms\Components\TextInput::make('title')
                            ->label('Step title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('category')
                            ->options([
                                'Getting Started' => 'Getting Started',
                                'Travel Orders' => 'Travel Orders',
                                'Approvals' => 'Approvals',
                                'Tips' => 'Tips',
                                'General' => 'General',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Only published steps are visible to all users.')
                            ->default(true),

                        Forms\Components\RichEditor::make('content')
                            ->label('Step instructions')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Prefer numbered lists (Step X.1, Step X.2, …).')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'link',
                                'redo',
                                'undo',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('sort_order')
                            ->label('Step')
                            ->formatStateUsing(fn ($state) => 'Step ' . (int) $state)
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('category')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('title')
                            ->label('Title')
                            ->weight('bold')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('content')
                            ->label('Instructions')
                            ->html()
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime('M d, Y g:i A')
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Step')
                    ->formatStateUsing(fn ($state) => 'Step ' . (int) $state)
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('What to do')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open step'),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserGuides::route('/'),
            'create' => Pages\CreateUserGuide::route('/create'),
            'view' => Pages\ViewUserGuide::route('/{record}'),
            'edit' => Pages\EditUserGuide::route('/{record}/edit'),
        ];
    }
}
