<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelApprovalResource\Pages;
use App\Models\TravelApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TravelApprovalResource extends Resource
{
    protected static ?string $model = TravelApproval::class;

    protected static ?string $navigationIcon  = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Travel Management';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $navigationLabel = 'Approvals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Travel Order Details')
                    ->schema([
                        Forms\Components\Placeholder::make('to_code')
                            ->label('TO Code')
                            ->content(fn($record) => $record->to_code ?? 'Pending'),

                        Forms\Components\Placeholder::make('requestor')
                            ->label('Requestor')
                            ->content(fn($record) => $record?->user?->name ?? '—'),

                        Forms\Components\Placeholder::make('dates')
                            ->label('Travel Dates')
                            ->content(fn($record) =>
                                $record?->travelOrder
                                    ? $record->travelOrder->start_date->format('M d, Y') .
                                      ' - ' .
                                      $record->travelOrder->end_date->format('M d, Y')
                                    : '—'
                            ),

                        Forms\Components\Placeholder::make('destination')
                            ->label('Destination/s')
                            ->content(fn($record) =>
                                collect($record?->travelOrder?->travel_location ?? [])
                                    ->pluck('destination')
                                    ->filter()
                                    ->implode(', ') ?: '—'
                            ),

                        Forms\Components\Placeholder::make('purpose')
                            ->content(fn($record) =>
                                $record?->travelOrder?->purpose ?? '—'
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Approval Action')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'APPROVED'     => 'Approve',
                                'REJECTED'     => 'Reject',
                                'FOR_REVISION' => 'Request Revision',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Textarea::make('reject_reason')
                            ->visible(fn(callable $get) => $get('status') === 'REJECTED')
                            ->required(fn(callable $get) => $get('status') === 'REJECTED')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('admin_to_override')
                            ->label('HR Override TO Code')
                            ->visible(fn() => Auth::user()->isHR())
                            ->helperText('Only HR officers can manually set the TO code.')
                            ->reactive(),

                        Forms\Components\Textarea::make('admin_override_reason')
                            ->label('Reason for Override')
                            ->required(fn(callable $get) => filled($get('admin_to_override')))
                            ->visible(fn(callable $get) =>
                                Auth::user()->isHR() &&
                                filled($get('admin_to_override'))
                            )
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
                ->where('approver_id', Auth::id())
                ->whereIn('status', ['PENDING', 'FOR_REVISION'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('to_code')
                    ->label('TO Code')
                    ->searchable()
                    ->default('Pending'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requestor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('travelOrder.start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                /*
                 * Destination is stored as a JSON array in travel_location.
                 * Each segment contains an origin and destination field.
                 */
                Tables\Columns\TextColumn::make('travelOrder.destination')
                    ->label('Destination')
                    ->wrap()
                    ->getStateUsing(fn($record) =>
                        collect($record->travelOrder?->travel_location ?? [])
                            ->pluck('destination')
                            ->filter()
                            ->implode(', ') ?: '—'
                    ),

                Tables\Columns\TextColumn::make('travelOrder.purpose')
                    ->label('Purpose')
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'PENDING'      => 'warning',
                        'APPROVED'     => 'success',
                        'REJECTED'     => 'danger',
                        'FOR_REVISION' => 'info',
                        'CANCELLED'    => 'gray',
                        default        => 'gray',
                    })
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Travel Order')
                    ->modalDescription('Are you sure you want to approve this travel order?')
                    ->visible(fn(TravelApproval $record) =>
                        $record->status === 'PENDING' &&
                        $record->approver_id === Auth::id()
                    )
                    ->form(fn() => Auth::user()->isHR() ? [
                        Forms\Components\TextInput::make('admin_to_override')
                            ->label('Override TO Code (optional)')
                            ->helperText('Leave blank to use the auto-generated code.'),
                        Forms\Components\Textarea::make('admin_override_reason')
                            ->label('Reason for Override (required if overriding)')
                            ->rows(2)
                            ->required(fn(callable $get) =>
                                filled($get('admin_to_override'))
                            ),
                    ] : [])
                    ->action(function (TravelApproval $record, array $data) {

                        if (!empty($data['admin_to_override']) && Auth::user()->isHR()) {
                            $record->travelOrder->update([
                                'to_code' => $data['admin_to_override'],
                            ]);
                            $record->update([
                                'to_code'               => $data['admin_to_override'],
                                'admin_override_by'     => Auth::id(),
                                'admin_override_reason' => $data['admin_override_reason'] ?? null,
                                'admin_override_at'     => now(),
                            ]);
                        }

                        app(\App\Services\TravelOrderService::class)
                            ->approveStep($record);

                        Notification::make()
                            ->title('Travel Order Approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Travel Order')
                    ->visible(fn(TravelApproval $record) =>
                        $record->status === 'PENDING' &&
                        $record->approver_id === Auth::id()
                    )
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('Rejection Reason')
                            ->placeholder('Please provide a clear reason for rejection...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(fn(TravelApproval $record, array $data) =>
                        app(\App\Services\TravelOrderService::class)
                            ->rejectStep($record, $data['reject_reason'])
                    ),

                Tables\Actions\Action::make('request_revision')
                    ->label('Request Revision')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Request Revision')
                    ->modalDescription('The travel order will be sent back to the requestor for editing.')
                    ->visible(fn(TravelApproval $record) =>
                        $record->status === 'PENDING' &&
                        $record->approver_id === Auth::id()
                    )
                    ->form([
                        Forms\Components\Textarea::make('revision_reason')
                            ->label('What needs to be revised?')
                            ->placeholder('e.g. Please revise the Travel Order due to changes in date, venue, travelers, funding source, and vehicle')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (TravelApproval $record, array $data) {
                        app(\App\Services\TravelOrderService::class)
                            ->requestRevision($record, $data['revision_reason']);

                        Notification::make()
                            ->title('Revision Requested')
                            ->warning()
                            ->body('The travel order has been sent back to the requestor for revision.')
                            ->send();
                    }),

                Tables\Actions\Action::make('view_pdf')
                    ->label('View PDF')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Travel Order Preview')
                    ->modalContent(fn(TravelApproval $record) =>
                        view('filament.modals.pdf-preview', [
                            'url' => route('travel-orders.preview-pdf', [
                                'order' => $record->travel_order_id,
                            ]),
                        ])
                    )
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTravelApprovals::route('/'),
            'view'  => Pages\ViewTravelApproval::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return TravelApproval::where('approver_id', Auth::id())
            ->whereIn('status', ['PENDING', 'FOR_REVISION'])
            ->count() ?: null;
    }
}
