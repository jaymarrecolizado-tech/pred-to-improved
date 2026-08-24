<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Admin Management';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int    $navigationSort  = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Performed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, 'OVERRIDE') => 'danger',
                        str_contains($state, 'ADD')      => 'success',
                        str_contains($state, 'CHANGE')   => 'warning',
                        str_contains($state, 'ASSIGN')   => 'info',
                        str_contains($state, 'CLEAR')    => 'gray',
                        default                          => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Record Type')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('model_id')
                    ->label('Record ID'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->wrap()
                    ->limit(80),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'CHANGE_APPROVER'       => 'Change Approver',
                        'ADD_APPROVAL_STEP'     => 'Add Approval Step',
                        'UPDATE_SNAPSHOT'       => 'Update Position Snapshot',
                        'ASSIGN_TO_CODE'        => 'Assign TO Code',
                        'CLEAR_PDF'             => 'Clear Stored PDF',
                        'OVERRIDE_TO_CONTENTS'  => 'Override TO Contents',
                        'CONFLICT_OVERRIDE'     => 'Conflict Override',
                    ]),

                Tables\Filters\SelectFilter::make('model')
                    ->options([
                        'TravelOrder'    => 'Travel Order',
                        'TravelApproval' => 'Travel Approval',
                    ]),
            ])
            ->bulkActions([
                ExportBulkAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        \pxlrbt\FilamentExcel\Columns\Column::make('created_at')->heading('Date & Time'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('user.name')->heading('Performed By'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('action')->heading('Action'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('model')->heading('Record Type'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('model_id')->heading('Record ID'),
                        \pxlrbt\FilamentExcel\Columns\Column::make('notes')->heading('Notes'),
                    ]),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
