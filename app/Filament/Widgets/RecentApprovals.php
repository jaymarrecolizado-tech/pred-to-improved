<?php

namespace App\Filament\Widgets;

use App\Models\TravelOrder;
use App\Filament\Resources\TravelOrderResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentApprovals extends BaseWidget
{
    protected static ?string $heading = 'Travel Orders';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return Auth::check();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn() => TravelOrder::query()
                    ->with(['user', 'approvals'])
                    ->when(
                        Auth::user()->isAdmin(),
                        fn($q) => $q->whereIn('status', [
                            'APPROVED', 'COMPLETED', 'REJECTED', 'CANCELLED', 'FOR_REVISION',
                        ])
                    )
                    ->when(
                        !Auth::user()->isAdmin(),
                        fn($q) => $q
                            ->where('user_id', Auth::id())
                            ->whereIn('status', [
                                'APPROVED', 'COMPLETED', 'REJECTED', 'CANCELLED', 'FOR_REVISION',
                            ])
                    )
                    ->orderBy('id', 'desc')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('to_code')
                    ->label('TO Code')
                    ->badge()
                    ->searchable(false)
                    ->color(fn($state, $record) => $state ? 'gray' : match($record->status) {
                        'REJECTED'              => 'danger',
                        'CANCELLED'             => 'gray',
                        'FOR_REVISION'          => 'info',
                        'PENDING', 'SUBMITTED'  => 'warning',
                        default                 => 'gray',
                    })
                    ->getStateUsing(fn($record) => $record->to_code ?? $record->status),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requestor')
                    ->description(fn($record) => $record->user->email ?? null)
                    ->icon('heroicon-m-user')
                    ->sortable()
                    ->visible(fn() => Auth::user()->isAdmin()),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Travel Date')
                    ->date()
                    ->description(fn($record) =>
                        'To: ' . optional($record->end_date)->format('M d, Y')
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'APPROVED'     => 'success',
                        'COMPLETED'    => 'success',
                        'REJECTED'     => 'danger',
                        'CANCELLED'    => 'gray',
                        'FOR_REVISION' => 'info',
                        default        => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Activity')
                    ->since()
                    ->description(fn($record) => $record->updated_at->format('M d, Y'))
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'APPROVED'     => 'Approved',
                        'COMPLETED'    => 'Completed',
                        'REJECTED'     => 'Rejected',
                        'CANCELLED'    => 'Cancelled',
                        'FOR_REVISION' => 'For Revision',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-magnifying-glass')
                    ->color('info')
                    ->button()
                    ->size('sm')
                    ->url(fn(TravelOrder $record) => TravelOrderResource::getUrl('view', [
                        'record' => $record->id,
                    ])),
            ])
            ->emptyStateHeading('No travel orders found')
            ->emptyStateDescription('Approved, completed, rejected, cancelled, and revised travel orders will appear here.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}