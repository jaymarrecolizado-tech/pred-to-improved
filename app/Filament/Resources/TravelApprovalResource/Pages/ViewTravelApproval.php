<?php

namespace App\Filament\Resources\TravelApprovalResource\Pages;

use App\Filament\Resources\TravelApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewTravelApproval extends ViewRecord
{
    protected static string $resource = TravelApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('warning')
                ->url($this->getResource()::getUrl('index')),

            Actions\Action::make('view_pdf')
                ->label('View PDF')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->modalHeading('Travel Order Preview')
                ->modalContent(fn($record) =>
                    view('filament.modals.pdf-preview', [
                        'url' => route('travel-orders.preview-pdf', [
                            'order' => $record->travel_order_id,
                        ]),
                    ])
                )
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Actions\Action::make('download_pdf')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->visible(fn($record) =>
                    in_array($record->travelOrder?->status, ['COMPLETED', 'CANCELLED'])
                )
                ->action(function ($record) {
                    $travelOrder = $record->travelOrder;

                    $travelOrder->load([
                        'user',
                        'approvals.workflow',
                        'approvals.approver.employee',
                    ]);

                    if (
                        $travelOrder->pdf_path &&
                        \Illuminate\Support\Facades\Storage::disk('public')->exists($travelOrder->pdf_path)
                    ) {
                        $storedContent = \Illuminate\Support\Facades\Storage::disk('public')
                            ->get($travelOrder->pdf_path);
                        $fileName = basename($travelOrder->pdf_path);

                        return response()->streamDownload(
                            function () use ($storedContent) {
                                echo $storedContent;
                            },
                            $fileName
                        );
                    }

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                        'pdf.TravelOrderCompleted',
                        ['travelOrder' => $travelOrder]
                    )->setPaper('a4', 'portrait');

                    $name      = $travelOrder->user->name ?? 'Unknown';
                    $nameParts = explode(' ', $name);
                    $lastName  = array_pop($nameParts);
                    $firstName = implode('', array_map(
                        fn($p) => ucfirst(strtolower($p)), $nameParts
                    ));
                    $date     = $travelOrder->start_date
                        ? \Carbon\Carbon::parse($travelOrder->start_date)->format('m.d.y')
                        : now()->format('m.d.y');
                    $fileName = 'TO.' . $lastName . '.' . $firstName . '.' . $date . '.pdf';

                    return response()->streamDownload(
                        function () use ($pdf) {
                            echo $pdf->output();
                        },
                        $fileName
                    );
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Approval Information')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('to_code')
                            ->label('TO Code')
                            ->default('Pending')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('workflow_step')
                            ->label('Step Number')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'PENDING'      => 'warning',
                                'APPROVED'     => 'success',
                                'REJECTED'     => 'danger',
                                'FOR_REVISION' => 'info',
                                'CANCELLED'    => 'gray',
                                default        => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Requestor')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('approver.name')
                            ->label('Approver')
                            ->icon('heroicon-o-user-circle'),

                        Infolists\Components\TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime()
                            ->formatStateUsing(fn($state) =>
                                $state ? $state->format('M d, Y H:i') : 'Not yet approved'
                            )
                            ->icon('heroicon-o-clock'),

                        Infolists\Components\TextEntry::make('reject_reason')
                            ->label('Rejection Reason')
                            ->visible(fn($record) => $record->status === 'REJECTED')
                            ->columnSpanFull()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('revision_reason')
                            ->label('Revision Reason')
                            ->visible(fn($record) => $record->status === 'FOR_REVISION')
                            ->columnSpanFull()
                            ->color('warning'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Travel Details')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Infolists\Components\TextEntry::make('travelOrder.start_date')
                            ->label('Overall Start Date')
                            ->date('M d, Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('travelOrder.end_date')
                            ->label('Overall End Date')
                            ->date('M d, Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('travelOrder.purpose')
                            ->label('Purpose')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('travelOrder.remarks')
                            ->label('Remarks')
                            ->placeholder('N/A')
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('travelOrder.travel_location')
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
                                        $state ? \Carbon\Carbon::parse($state)->format('M d, Y') : '—'
                                    ),
                                Infolists\Components\TextEntry::make('end_date')
                                    ->icon('heroicon-o-calendar-days')
                                    ->label('End Date')
                                    ->formatStateUsing(fn($state) =>
                                        $state ? \Carbon\Carbon::parse($state)->format('M d, Y') : '—'
                                    ),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->grid(1)
                            ->state(fn($record) => $record->travelOrder?->travel_location ?? []),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Budget & Vehicle')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Infolists\Components\TextEntry::make('travelOrder.travel_sources')
                            ->listWithLineBreaks()
                            ->label('Travel Source')
                            ->icon('heroicon-o-currency-dollar')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('travelOrder.vehicle')
                            ->label('Assigned Vehicle')
                            ->placeholder('No vehicle assigned')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No vehicle assigned';
                                return str_replace('|', ' - ', $state);
                            })
                            ->icon('heroicon-o-truck'),

                        Infolists\Components\TextEntry::make('travelOrder.other_funds')
                            ->label('Other Funds')
                            ->placeholder('N/A')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-currency-dollar')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'N/A';
                                $labels = [
                                    'actual'        => 'Actual Expenses',
                                    'incidental'    => 'Incidental Expenses',
                                    'per_diem'      => 'Per Diem',
                                    'official_time' => 'Official Time',
                                    'no_claim'      => 'No Claim',
                                ];
                                return collect((array) $state)
                                    ->map(fn($item) => $labels[$item] ?? $item)
                                    ->implode(', ');
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Travelers')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('travelOrder.travelers')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Employee Name')
                                    ->weight('bold')
                                    ->icon('heroicon-o-user'),
                                Infolists\Components\TextEntry::make('position')
                                    ->label('Position')
                                    ->icon('heroicon-o-briefcase'),
                                Infolists\Components\TextEntry::make('division')
                                    ->label('Division')
                                    ->badge()
                                    ->color('primary'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->state(function ($record) {
                                $travelers = $record->travelOrder?->travelers;
                                if (is_string($travelers)) {
                                    return json_decode($travelers, true);
                                }
                                return $travelers;
                            }),
                    ]),

                Infolists\Components\Section::make('Attachments')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Infolists\Components\TextEntry::make('travelOrder.attachment')
                            ->label('')
                            ->html()
                            ->getStateUsing(function ($record) {
                                $files = collect((array) ($record->travelOrder?->attachment ?? []))->filter();
                                if ($files->isEmpty()) return '<em style="color:#9ca3af;">No attachments</em>';

                                return $files->map(function ($path) {
                                    $encodedPath = implode('/', array_map(
                                        'rawurlencode',
                                        explode('/', rawurldecode($path))
                                    ));
                                    $url  = asset('storage/' . $encodedPath);
                                    $name = basename($path);
                                    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                    $icon = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? '🖼️' : '📄';

                                    return "<a href=\"{$url}\" target=\"_blank\" style=\"display:inline-flex;align-items:center;gap:6px;padding:8px 14px;margin:4px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;color:#1d4ed8;text-decoration:none;font-size:13px;font-weight:500;\">
                                        {$icon} {$name}
                                    </a>";
                                })->implode('');
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => !empty($record->travelOrder?->attachment)),
            ]);
    }
}
