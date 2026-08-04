<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use App\Services\TravelOrderService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ViewTravelOrder extends ViewRecord
{
    protected static string $resource = TravelOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn() =>
                    $this->record->status === 'COMPLETED' ||
                    ($this->record->status === 'CANCELLED' && $this->record->to_code)
                )
                ->action(function () {
                    $this->record->load([
                        'user',
                        'approvals.workflow',
                        'approvals.approver.employee',
                    ]);

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.TravelOrderCompleted', [
                        'travelOrder' => $this->record,
                    ])->setPaper('a4', 'portrait');

                    $name      = $this->record->user->name ?? 'Unknown';
                    $nameParts = explode(' ', $name);
                    $lastName  = array_pop($nameParts);
                    $firstName = implode('', array_map(
                        fn($p) => ucfirst(strtolower($p)), $nameParts
                    ));
                    $date     = $this->record->start_date
                        ? Carbon::parse($this->record->start_date)->format('m.d.y')
                        : now()->format('m.d.y');
                    $fileName = 'TO.' . $lastName . '.' . $firstName . '.' . $date . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $fileName);
                }),

            Actions\Action::make('resubmit')
                ->label('Resubmit for Approval')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn() =>
                    $this->record->status === 'FOR_REVISION' &&
                    $this->record->user_id === auth()->id()
                )
                ->requiresConfirmation()
                ->modalHeading('Resubmit Travel Order')
                ->modalDescription('Your travel order will be sent back to the approver who requested the revision.')
                ->action(function () {
                    app(TravelOrderService::class)
                        ->resubmitAfterRevision($this->record);

                    Notification::make()
                        ->title('Travel Order Resubmitted')
                        ->success()
                        ->body('Your travel order has been sent back for approval.')
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('cancel')
                ->label('Cancel Travel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() =>
                    in_array($this->record->status, ['SUBMITTED', 'PENDING', 'APPROVED', 'COMPLETED', 'FOR_REVISION']) &&
                    $this->record->user_id === auth()->id()
                )
                ->requiresConfirmation()
                ->modalHeading('Cancel Travel Order')
                ->modalDescription('Are you sure? The TO number will be preserved for audit purposes.')
                ->form([
                    Forms\Components\Textarea::make('cancel_reason')
                        ->label('Reason for Cancellation')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    app(TravelOrderService::class)
                        ->cancelOrder($this->record, $data['cancel_reason']);

                    Notification::make()
                        ->title('Travel Order Cancelled')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'cancel_reason', 'cancelled_at']);
                }),

            Actions\EditAction::make()
                ->icon('heroicon-o-pencil')
                ->visible(fn() =>
                    in_array($this->record->status, ['DRAFT', 'FOR_REVISION']) &&
                    $this->record->user_id === auth()->id()
                ),

            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn() =>
                    $this->record->status === 'DRAFT' &&
                    $this->record->user_id === auth()->id()
                ),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Travel Order Information')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('to_code')
                            ->label('TO Code')
                            ->icon('heroicon-o-identification')
                            ->badge()
                            ->color('gray')
                            ->default('Pending'),

                        Infolists\Components\TextEntry::make('status')
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'DRAFT'        => 'gray',
                                'SUBMITTED'    => 'info',
                                'PENDING'      => 'warning',
                                'APPROVED',
                                'COMPLETED'    => 'success',
                                'REJECTED'     => 'danger',
                                'CANCELLED'    => 'gray',
                                'FOR_REVISION' => 'info',
                                default        => 'secondary',
                            }),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Requestor')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->icon('heroicon-o-clock')
                            ->formatStateUsing(function ($state) {
                                try {
                                    return $state
                                        ? Carbon::parse($state)->format('M d, Y H:i')
                                        : 'Not submitted';
                                } catch (\Exception $e) {
                                    return 'Not submitted';
                                }
                            }),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Revision Details')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Infolists\Components\TextEntry::make('revised_at')
                            ->label('Revision Requested At')
                            ->icon('heroicon-o-clock')
                            ->formatStateUsing(fn($state) =>
                                $state ? Carbon::parse($state)->format('M d, Y H:i') : 'N/A'
                            ),

                        Infolists\Components\TextEntry::make('revision_reason')
                            ->label('What Needs to be Revised')
                            ->icon('heroicon-o-exclamation-circle')
                            ->color('warning')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn() => $this->record->status === 'FOR_REVISION'),

                Infolists\Components\Section::make('Cancellation Details')
                    ->icon('heroicon-o-x-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('cancelled_at')
                            ->label('Cancelled At')
                            ->icon('heroicon-o-clock')
                            ->formatStateUsing(fn($state) =>
                                $state ? Carbon::parse($state)->format('M d, Y H:i') : 'N/A'
                            ),

                        Infolists\Components\TextEntry::make('cancel_reason')
                            ->label('Reason for Cancellation')
                            ->icon('heroicon-o-exclamation-circle')
                            ->color('danger')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn() => $this->record->status === 'CANCELLED'),

                Infolists\Components\Section::make('Travel Details')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->icon('heroicon-o-calendar')
                            ->label('Overall Start Date')
                            ->formatStateUsing(fn($state) =>
                                $state ? Carbon::parse($state)->format('M d, Y') : 'N/A'
                            ),

                        Infolists\Components\TextEntry::make('end_date')
                            ->icon('heroicon-o-calendar-days')
                            ->label('Overall End Date')
                            ->formatStateUsing(fn($state) =>
                                $state ? Carbon::parse($state)->format('M d, Y') : 'N/A'
                            ),

                        Infolists\Components\RepeatableEntry::make('travel_location')
                            ->label('Itinerary / Segments')
                            ->schema([
                                Infolists\Components\TextEntry::make('origin')
                                    ->icon('heroicon-o-map-pin')
                                    ->label('Origin'),
                                Infolists\Components\TextEntry::make('destination')
                                    ->icon('heroicon-o-truck')
                                    ->label('Destination'),
                                Infolists\Components\TextEntry::make('start_date')
                                    ->icon('heroicon-o-calendar')
                                    ->label('Start Date')
                                    ->formatStateUsing(fn($state) =>
                                        $state ? Carbon::parse($state)->format('M d, Y') : '—'
                                    ),
                                Infolists\Components\TextEntry::make('end_date')
                                    ->icon('heroicon-o-calendar-days')
                                    ->label('End Date')
                                    ->formatStateUsing(fn($state) =>
                                        $state ? Carbon::parse($state)->format('M d, Y') : '—'
                                    ),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->grid(1),

                        Infolists\Components\TextEntry::make('purpose')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->label('Purpose')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('remarks')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->label('Remarks')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Budget & Vehicle')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Infolists\Components\TextEntry::make('travel_sources')
                            ->icon('heroicon-o-wallet')
                            ->label('Travel Source')
                            ->listWithLineBreaks()
                            ->badge(),

                        Infolists\Components\TextEntry::make('vehicle')
                            ->icon('heroicon-o-truck')
                            ->label('Vehicle')
                            ->placeholder('Not specified')
                            ->listWithLineBreaks()
                            ->badge()
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return 'Not specified';
                                }

                                $parts = explode('|', (string) $state);

                                return count($parts) === 2
                                    ? "{$parts[0]} - {$parts[1]}"
                                    : $state;
                            }),

                        Infolists\Components\TextEntry::make('other_funds')
                            ->icon('heroicon-o-currency-dollar')
                            ->label('Other Funds')
                            ->placeholder('N/A')
                            ->listWithLineBreaks(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Travelers')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('travelers')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->icon('heroicon-o-user'),
                                Infolists\Components\TextEntry::make('position')
                                    ->icon('heroicon-o-briefcase'),
                                Infolists\Components\TextEntry::make('division')
                                    ->icon('heroicon-o-building-office-2'),
                            ])
                            ->columns(3),
                    ]),

                Infolists\Components\Section::make('Approvals')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('approvals')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('approver.name')
                                    ->label('Approver')
                                    ->icon('heroicon-o-user-circle'),

                                Infolists\Components\TextEntry::make('status')
                                    ->icon('heroicon-o-adjustments-horizontal')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'PENDING'      => 'warning',
                                        'APPROVED'     => 'success',
                                        'REJECTED'     => 'danger',
                                        'CANCELLED'    => 'gray',
                                        'FOR_REVISION' => 'info',
                                        default        => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => $state ?: 'PENDING'),

                                Infolists\Components\TextEntry::make('reject_reason')
                                    ->label('Rejection Reason')
                                    ->icon('heroicon-o-exclamation-circle')
                                    ->visible(fn($record) =>
                                        isset($record->status) &&
                                        $record->status === 'REJECTED'
                                    )
                                    ->color('danger')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('revision_reason')
                                    ->label('Revision Reason')
                                    ->icon('heroicon-o-pencil-square')
                                    ->visible(fn($record) =>
                                        isset($record->status) &&
                                        $record->status === 'FOR_REVISION'
                                    )
                                    ->color('warning')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn() => $this->record->approvals->count() > 0),

                Infolists\Components\Section::make('Attachments')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Infolists\Components\TextEntry::make('attachment')
                            ->label('')
                            ->html()
                            ->getStateUsing(function ($record) {
                                $files = collect((array) ($record->attachment ?? []))->filter();
                                if ($files->isEmpty()) return '<em style="color:#9ca3af;">No attachments</em>';

                                return $files->map(function ($path) {
                                    /*
                                     * Encode each path segment individually to handle
                                     * filenames with spaces or special characters.
                                     * Preserves the directory separator slash.
                                     */
                                    $encodedPath = implode('/', array_map(
                                        'rawurlencode',
                                        explode('/', rawurldecode($path))
                                    ));
                                    $url  = asset('storage/' . $encodedPath);
                                    $name = basename($path);
                                    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                    $icon = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? '🖼️' : '📄';

                                    return "<a href=\"{$url}\" target=\"_blank\" class=\"dict-attach-chip\">
                                        {$icon} {$name}
                                    </a>";
                                })->implode('');
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => !empty($record->attachment)),
            ]);
    }
}