<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatchNoteResource\Pages;
use App\Filament\Resources\PatchNoteResource\Widgets\PatchNotesWidget;
use App\Models\PatchNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PatchNoteResource extends Resource
{
    protected static ?string $model = PatchNote::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Patch Notes';
    protected static ?string $pluralModelLabel = 'Patch Notes';

    /*
    |--------------------------------------------------------------------------
    | ACCESS CONTROL
    | Only Super Admin (MISS) can create, edit, and delete patch notes.
    | All authenticated users can view patch notes.
    |--------------------------------------------------------------------------
    */

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->check()
            && auth()->user()->isSuperAdmin();
    }

    public static function canEdit($record): bool
    {
        return auth()->check()
            && auth()->user()->isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        return auth()->check()
            && auth()->user()->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(5),

                Forms\Components\Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()->isSuperAdmin()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->isSuperAdmin()),
                ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | WIDGETS
    |--------------------------------------------------------------------------
    */

    public static function getWidgets(): array
    {
        return [
            PatchNotesWidget::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPatchNotes::route('/'),
            'create' => Pages\CreatePatchNote::route('/create'),
            'view'   => Pages\ViewPatchNote::route('/{record}'),
            'edit'   => Pages\EditPatchNote::route('/{record}/edit'),
        ];
    }
}