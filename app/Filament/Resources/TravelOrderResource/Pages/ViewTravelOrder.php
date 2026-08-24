<?php

namespace App\Filament\Resources\TravelOrderResource\Pages;

use App\Filament\Resources\TravelOrderResource;
use App\Models\AuditLog;
use App\Models\TravelApproval;
use App\Models\TravelOrder;
use App\Models\TravelWorkflow;
use App\Models\User;
use App\Services\TravelOrderService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ViewTravelOrder extends ViewRecord
{
    protected static string $resource = TravelOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //View PDF
            
            Actions\Action::make('view_pdf')
                ->label('View PDF')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() =>
                    $this->record->user_id === auth()->id() ||
                    auth()->user()->isAdmin()
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

            /*
             * Download PDF — only for COMPLETED or CANCELLED orders.
             */
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

                    if (
                        $this->record->pdf_path &&
                        Storage::disk('public')->exists($this->record->pdf_path)
                    ) {
                        $storedContent = Storage::disk('public')->get($this->record->pdf_path);
                        $fileName      = basename($this->record->pdf_path);

                        return response()->streamDownload(function () use ($storedContent) {
                            echo $storedContent;
                        }, $fileName);
                    }

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

            //Super Admin - Change Approver on a pending approval step.
            
            Actions\Action::make('change_approver')
                ->label('Change Approver')
                ->icon('heroicon-o-user-minus')
                ->color('warning')
                ->visible(fn() => auth()->user()->isSuperAdmin())
                ->form([
                    Forms\Components\Select::make('approval_id')
                        ->label('Select Approval Step to Change')
                        ->options(fn() =>
                            $this->record->approvals()
                                ->where('status', 'PENDING')
                                ->with('approver')
                                ->get()
                                ->mapWithKeys(fn($a) => [
                                    $a->id => 'Step ' . $a->workflow_step . ' — ' . $a->approver->name
                                ])
                        )
                        ->required(),

                    Forms\Components\Select::make('new_approver_id')
                        ->label('New Approver')
                        ->options(fn() =>
                            User::whereIn('role', ['admin', 'super_admin', 'hr'])
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Reason for Change')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $approval    = TravelApproval::find($data['approval_id']);
                    $oldApprover = $approval->approver->name;
                    $newApprover = User::find($data['new_approver_id']);

                    $approval->update(['approver_id' => $newApprover->id]);

                    AuditLog::logOverride(
                        action:   'CHANGE_APPROVER',
                        model:    'TravelApproval',
                        modelId:  $approval->id,
                        oldValue: ['approver' => $oldApprover],
                        newValue: ['approver' => $newApprover->name],
                        notes:    $data['notes']
                    );

                    Notification::make()
                        ->title('Approver Changed')
                        ->success()
                        ->body('Step ' . $approval->workflow_step . ' now assigned to ' . $newApprover->name)
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            //Super Admin - Add a missing approval step.
            
            Actions\Action::make('add_approval_step')
                ->label('Add Approval Step')
                ->icon('heroicon-o-plus-circle')
                ->color('info')
                ->visible(fn() => auth()->user()->isSuperAdmin())
                ->form([
                    Forms\Components\Select::make('approver_id')
                        ->label('Approver')
                        ->options(fn() =>
                            User::whereIn('role', ['admin', 'super_admin', 'hr'])
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('workflow_id')
                        ->label('Approval Role')
                        ->options([
                            6   => 'TOD Chief Recommend (Bariuan)',
                            12  => 'TOD Chief Recommend (Magdalena)',
                            37  => 'ARD Initial (Laverinto)',
                            99  => 'Admin Chief Initial (Mina)',
                            407 => 'Admin Chief Initial (Jemar)',
                            543 => 'Regional Director Approve (Pinky)',
                            550 => 'HR TO Code Provider',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('position_snapshot')
                        ->label('Position/Designation to show on PDF')
                        ->required(),

                    Forms\Components\TextInput::make('to_code')
                        ->label('TO Code (if assigning)')
                        ->default(fn() => $this->record->to_code ?? ''),

                    Forms\Components\Textarea::make('notes')
                        ->label('Reason for Adding Step')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $approver = User::find($data['approver_id']);

                    TravelApproval::create([
                        'travel_order_id'             => $this->record->id,
                        'user_id'                     => $this->record->user_id,
                        'approver_id'                 => $approver->id,
                        'workflow_step'               => $data['workflow_id'],
                        'status'                      => 'APPROVED',
                        'approved_at'                 => now(),
                        'to_code'                     => $data['to_code'] ?: null,
                        'approver_name_snapshot'      => $approver->name,
                        'approver_position_snapshot'  => $data['position_snapshot'],
                        'approver_signature_snapshot' => $approver->signature,
                    ]);

                    AuditLog::logOverride(
                        action:   'ADD_APPROVAL_STEP',
                        model:    'TravelOrder',
                        modelId:  $this->record->id,
                        oldValue: [],
                        newValue: [
                            'approver'  => $approver->name,
                            'position'  => $data['position_snapshot'],
                            'to_code'   => $data['to_code'],
                        ],
                        notes: $data['notes']
                    );

                    // Clear stored PDF so it regenerates with new approval
                    $this->record->update(['pdf_path' => null]);

                    Notification::make()
                        ->title('Approval Step Added')
                        ->success()
                        ->body($approver->name . ' added as approver.')
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            //Super Admin - Update position snapshot on a completed approval.
            
            Actions\Action::make('update_snapshot')
                ->label('Update Position Snapshot')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible(fn() => auth()->user()->isSuperAdmin())
                ->form([
                    Forms\Components\Select::make('approval_id')
                        ->label('Select Approval Step')
                        ->options(fn() =>
                            $this->record->approvals()
                                ->with('approver')
                                ->get()
                                ->mapWithKeys(fn($a) => [
                                    $a->id => 'Step ' . $a->workflow_step .
                                              ' — ' . $a->approver->name .
                                              ' [' . ($a->approver_position_snapshot ?? 'No snapshot') . ']'
                                ])
                        )
                        ->required(),

                    Forms\Components\TextInput::make('new_position')
                        ->label('New Position/Designation')
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Reason for Change')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $approval    = TravelApproval::find($data['approval_id']);
                    $oldPosition = $approval->approver_position_snapshot;

                    $approval->update([
                        'approver_position_snapshot' => $data['new_position'],
                    ]);

                    // Clear stored PDF so it regenerates with new position
                    $this->record->update(['pdf_path' => null]);

                    AuditLog::logOverride(
                        action:   'UPDATE_SNAPSHOT',
                        model:    'TravelApproval',
                        modelId:  $approval->id,
                        oldValue: ['position' => $oldPosition],
                        newValue: ['position'  => $data['new_position']],
                        notes:    $data['notes']
                    );

                    Notification::make()
                        ->title('Position Snapshot Updated')
                        ->success()
                        ->send();
                }),

            //Super Admin - Assign or change TO code.
            
            Actions\Action::make('assign_to_code')
                ->label('Assign TO Code')
                ->icon('heroicon-o-hashtag')
                ->color('info')
                ->visible(fn() => auth()->user()->isSuperAdmin())
                ->form([
                    Forms\Components\TextInput::make('to_code')
                        ->label('TO Code')
                        ->default(fn() => $this->record->to_code ?? '')
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Reason')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $oldCode = $this->record->to_code;

                    $this->record->update([
                        'to_code'  => $data['to_code'],
                        'pdf_path' => null,
                    ]);

                    TravelApproval::where('travel_order_id', $this->record->id)
                        ->update(['to_code' => $data['to_code']]);

                    AuditLog::logOverride(
                        action:   'ASSIGN_TO_CODE',
                        model:    'TravelOrder',
                        modelId:  $this->record->id,
                        oldValue: ['to_code' => $oldCode],
                        newValue: ['to_code'  => $data['to_code']],
                        notes:    $data['notes']
                    );

                    Notification::make()
                        ->title('TO Code Assigned')
                        ->success()
                        ->body('TO Code set to ' . $data['to_code'])
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            /*
             * Super Admin — Clear stored PDF to force regeneration.
             */
            Actions\Action::make('clear_pdf')
                ->label('Clear Stored PDF')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn() =>
                    auth()->user()->isSuperAdmin() &&
                    $this->record->pdf_path
                )
                ->requiresConfirmation()
                ->modalHeading('Clear Stored PDF')
                ->modalDescription('The stored PDF will be deleted. Next download will regenerate it with current data.')
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Reason for Clearing PDF')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $oldPath = $this->record->pdf_path;

                    $this->record->update(['pdf_path' => null]);

                    AuditLog::logOverride(
                        action:   'CLEAR_PDF',
                        model:    'TravelOrder',
                        modelId:  $this->record->id,
                        oldValue: ['pdf_path' => $oldPath],
                        newValue: ['pdf_path'  => null],
                        notes:    $data['notes']
                    );

                    Notification::make()
                        ->title('Stored PDF Cleared')
                        ->success()
                        ->body('Next download will regenerate the PDF with current data.')
                        ->send();
                }),

            /*
             * Super Admin — Override TO contents (travelers, dates, segments, purpose).
             */
            Actions\Action::make('override_contents')
                ->label('Override TO Contents')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('danger')
                ->visible(fn() => auth()->user()->isSuperAdmin())
                ->form([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Overall Start Date')
                        ->native(false)
                        ->default(fn() => $this->record->start_date),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Overall End Date')
                        ->native(false)
                        ->default(fn() => $this->record->end_date),

                    Forms\Components\Textarea::make('purpose')
                        ->label('Purpose')
                        ->rows(3)
                        ->default(fn() => $this->record->purpose),

                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(2)
                        ->default(fn() => $this->record->remarks),

                    Forms\Components\Textarea::make('notes')
                        ->label('Reason for Override')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $oldValues = [
                        'start_date' => $this->record->start_date,
                        'end_date'   => $this->record->end_date,
                        'purpose'    => $this->record->purpose,
                        'remarks'    => $this->record->remarks,
                    ];

                    $this->record->update([
                        'start_date' => $data['start_date'],
                        'end_date'   => $data['end_date'],
                        'purpose'    => $data['purpose'],
                        'remarks'    => $data['remarks'],
                        'pdf_path'   => null,
                    ]);

                    AuditLog::logOverride(
                        action:   'OVERRIDE_TO_CONTENTS',
                        model:    'TravelOrder',
                        modelId:  $this->record->id,
                        oldValue: $oldValues,
                        newValue: [
                            'start_date' => $data['start_date'],
                            'end_date'   => $data['end_date'],
                            'purpose'    => $data['purpose'],
                            'remarks'    => $data['remarks'],
                        ],
                        notes: $data['notes']
                    );

                    Notification::make()
                        ->title('Travel Order Updated')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
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
                            ->formatStateUsing(function ($state) {
                                if (!$state) return 'Not specified';
                                $parts = explode('|', $state);
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
                    ->visible(fn($record) => !empty($record->attachment)),
            ]);
    }
}
