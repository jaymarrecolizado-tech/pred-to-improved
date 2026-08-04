<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelOrderResource\Pages;
use App\Models\TravelOrder;
use App\Models\TravelSource;
use App\Models\TravelWorkflow;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Services\TravelOrderService;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;

class TravelOrderResource extends Resource
{
    protected static ?string $model = TravelOrder::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Travel Management';
    protected static ?int    $navigationSort  = 3;

    public static function getEloquentQuery(): Builder
    {
        $query    = parent::getEloquentQuery();
        $userId   = auth()->id();
        $user     = auth()->user();
        $userName = $user->name;

        if ($user->isAdmin()) {
            return $query->where(function ($q) use ($userId) {
                $q->where('status', '!=', 'DRAFT')
                  ->orWhere('user_id', $userId);
            });
        }

        return $query->where(function ($q) use ($userId, $userName) {
            $q->where('user_id', $userId);

            if ($userName) {
                $q->orWhereRaw(
                    "JSON_CONTAINS(travelers, JSON_OBJECT('name', ?), '$')",
                    [$userName]
                );
            }
        });
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $record->user_id === $user->id;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $record->user_id === $user->id;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Travel Period')
                    ->icon('heroicon-o-calendar')
                    ->description('Set the overall start and end dates for the entire travel order.')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->native(false)
                            ->minDate(fn($record) =>
                                $record?->status === 'FOR_REVISION' ? null : today()
                            )
                            ->reactive()
                            ->helperText(fn($record) =>
                                $record?->status === 'FOR_REVISION'
                                    ? 'Revision mode — you may keep or update existing dates.'
                                    : null
                            ),

                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->minDate(fn(callable $get, $record) =>
                                $record?->status === 'FOR_REVISION'
                                    ? null
                                    : ($get('start_date') ?? today())
                            )
                            ->reactive(),
                    ])->columns(2),

                Section::make('Revision Required')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\Placeholder::make('revision_notice')
                            ->label('What needs to be revised')
                            ->content(fn($record) => $record?->revision_reason ?? '—'),
                    ])
                    ->visible(fn($record) => $record?->status === 'FOR_REVISION')
                    ->collapsible(),

                Section::make('Itinerary (Travel Locations)')
                    ->description('Add one or more trip segments.')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Repeater::make('travel_location')
                            ->label('Trip Segments')
                            ->schema([
                                TextInput::make('origin')
                                    ->label('Origin')
                                    ->placeholder('e.g. DICT Regional Office II')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('destination')
                                    ->label('Destination')
                                    ->placeholder('e.g. DICT Provincial Offices')
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Segment Start Date')
                                    ->native(false)
                                    ->nullable()
                                    ->minDate(fn(callable $get, $record) =>
                                        $record?->status === 'FOR_REVISION'
                                            ? null
                                            : ($get('../../start_date') ?? today())
                                    )
                                    ->reactive()
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Segment End Date')
                                    ->native(false)
                                    ->nullable()
                                    ->minDate(fn(callable $get, $record) =>
                                        $record?->status === 'FOR_REVISION'
                                            ? null
                                            : ($get('start_date') ?? $get('../../start_date') ?? today())
                                    )
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add Location Segment')
                            ->reorderable()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('purpose')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Budget & Vehicle')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Select::make('travel_sources')
                            ->multiple()
                            ->label('Travel Source/Budget')
                            ->options(TravelSource::all()->pluck('name', 'name'))
                            ->required()
                            ->helperText('Select multiple if applicable'),

                        Select::make('vehicle')
                            ->multiple()
                            ->label('Vehicle')
                            ->options(function (callable $get) {
                                $sources = TravelSource::all();
                                $options = [];
                                foreach ($sources as $source) {
                                    if (!$source->vehicles) continue;
                                    foreach ($source->vehicles as $vehicle) {
                                        $key = $vehicle['car_name'] . '|' . $vehicle['plate_number'];
                                        $options[$key] = $vehicle['car_name'] . ' - ' . $vehicle['plate_number'];
                                    }
                                }
                                return $options;
                            })
                            ->searchable()
                            ->helperText('Select multiple if applicable'),

                        Forms\Components\CheckboxList::make('other_funds')
                            ->label('Additional Travel Expenses')
                            ->options([
                                'actual'        => 'Actual Expenses',
                                'per_diem'      => 'Per Diem',
                                'official_time' => 'Official Time',
                                'no_claim'      => 'No claim',
                            ])
                            ->columns(2),
                    ])->columns(2),

                Section::make('Traveler/s')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Repeater::make('travelers')
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->options(
                                        fn() => Employee::all()->mapWithKeys(
                                            fn($emp) => [$emp->id => $emp->full_name]
                                        )
                                    )
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $employee = Employee::with('division')->find($state);
                                            if ($employee) {
                                                $set('employee_id', (int) $state);
                                                $set('name', $employee->full_name);
                                                $set('position', $employee->position);
                                                $set('division', $employee->division?->name);
                                            }
                                        }
                                    }),
                                Forms\Components\Hidden::make('name'),
                                Forms\Components\Hidden::make('position'),
                                Forms\Components\Hidden::make('division'),
                                Forms\Components\Placeholder::make('Info')
                                    ->content(fn($get) => $get('employee_id')
                                        ? ($get('position') . ' - ' . $get('division'))
                                        : 'Select an employee'),
                            ])
                            ->columns(1)
                            ->addActionLabel('Add Traveler'),
                    ]),

                Section::make('Workflow & Approvals')
                    ->schema([
                        Forms\Components\CheckboxList::make('workflow_steps')
                            ->label('Select Approval Steps')
                            ->hint(fn() => TravelWorkflow::where('user_id', Auth::id())
                                ->active()->exists()
                                ? null
                                : 'No workflows found. Please create one first.')
                            ->hintColor('danger')
                            ->options(
                                fn() => TravelWorkflow::where('user_id', Auth::id())
                                    ->active()
                                    ->ordered()
                                    ->get()
                                    ->mapWithKeys(fn($w) => [
                                        $w->id => "Step {$w->workflow_step}: {$w->approver->name}"
                                    ])
                            )
                            ->helperText(fn($state) => empty($state) &&
                                !TravelWorkflow::where('user_id', Auth::id())
                                    ->active()->exists()
                                ? 'Create travel workflow first...'
                                : null)
                            ->required()
                            ->columns(2),
                    ]),

                Section::make('Attachments')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Upload Files')
                            ->directory('travel-orders')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->preserveFilenames()
                            ->multiple()
                            ->maxFiles(5)
                            ->reorderable()
                            ->appendFiles()
                            ->helperText('Upload up to 5 files. Accepted: images and PDF. Max 5MB each.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        TextInput::make('to_code')
                            ->label('T.O. Code')
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                'DRAFT'        => 'Draft',
                                'SUBMITTED'    => 'Submitted',
                                'PENDING'      => 'Pending',
                                'APPROVED'     => 'Approved',
                                'REJECTED'     => 'Rejected',
                                'COMPLETED'    => 'Completed',
                                'CANCELLED'    => 'Cancelled',
                                'FOR_REVISION' => 'For Revision',
                            ])
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->hidden(fn($record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('to_code')
                    ->label('T.O. No.')
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
                    ->searchable()
                    ->visible(fn() => auth()->user()->isAdmin()),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('destination/s')
                    ->label('Destination/s')
                    ->wrap()
                    ->limit(50)
                    ->getStateUsing(fn($record) =>
                        collect($record->travel_location ?? [])
                            ->pluck('destination')
                            ->filter()
                            ->implode(', ') ?: '—'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'DRAFT'                 => 'gray',
                        'SUBMITTED', 'PENDING'  => 'warning',
                        'APPROVED', 'COMPLETED' => 'success',
                        'REJECTED'              => 'danger',
                        'CANCELLED'             => 'gray',
                        'FOR_REVISION'          => 'info',
                        default                 => 'gray',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\Action::make('submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn($record) =>
                        $record->status === 'DRAFT' &&
                        $record->user_id === auth()->id()
                    )
                    ->requiresConfirmation()
                    ->action(function (TravelOrder $record) {
                        $record->update([
                            'status'       => 'SUBMITTED',
                            'submitted_at' => now(),
                        ]);
                        app(TravelOrderService::class)->createApprovals($record);
                        Notification::make()
                            ->title('Order Submitted')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('follow_up')
                    ->label('Follow Up')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->visible(fn($record) =>
                        in_array($record->status, ['PENDING', 'SUBMITTED']) &&
                        $record->user_id === auth()->id()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Send Follow Up')
                    ->modalDescription('This will send a reminder notification to the current approver.')
                    ->action(function (TravelOrder $record) {
                        $currentApproval = $record->approvals()
                            ->where('status', 'PENDING')
                            ->orderBy('id')
                            ->first();

                        if (!$currentApproval) {
                            Notification::make()
                                ->title('No pending approver found')
                                ->warning()
                                ->send();
                            return;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Follow Up: Travel Order Awaiting Your Approval')
                            ->warning()
                            ->icon('heroicon-o-bell-alert')
                            ->body("{$record->user->name} is following up on their travel order" .
                                ($record->to_code ? " ({$record->to_code})" : '') .
                                ". Please review at your earliest convenience.")
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url("/DICT/travel-approvals"),
                            ])
                            ->sendToDatabase($currentApproval->approver);

                        try {
                            $currentApproval->approver->notify(
                                new \App\Notifications\TravelOrderFollowUp($record, $currentApproval)
                            );
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error(
                                'Failed to send follow up email: ' . $e->getMessage()
                            );
                        }

                        Notification::make()
                            ->title('Follow up sent to ' . $currentApproval->approver->name)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('resubmit')
                    ->label('Resubmit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn($record) =>
                        $record->status === 'FOR_REVISION' &&
                        $record->user_id === auth()->id()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Resubmit Travel Order')
                    ->modalDescription('Your travel order will be sent back to the approver who requested the revision.')
                    ->action(function (TravelOrder $record) {
                        app(TravelOrderService::class)->resubmitAfterRevision($record);

                        Notification::make()
                            ->title('Travel Order Resubmitted')
                            ->success()
                            ->body('Your travel order has been sent back for approval.')
                            ->send();
                    }),

                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn($record) => in_array($record->status, ['COMPLETED', 'CANCELLED']))
                    ->action(function (TravelOrder $record) {
                        $record->load([
                            'user',
                            'approvals.workflow',
                            'approvals.approver.employee',
                        ]);

                        if (
                            $record->pdf_path &&
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($record->pdf_path)
                        ) {
                            $storedContent = \Illuminate\Support\Facades\Storage::disk('public')
                                ->get($record->pdf_path);
                            $fileName = basename($record->pdf_path);

                            return response()->streamDownload(
                                function () use ($storedContent) {
                                    echo $storedContent;
                                },
                                $fileName
                            );
                        }

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                            'pdf.TravelOrderCompleted',
                            ['travelOrder' => $record]
                        )->setPaper('a4', 'portrait');

                        $name      = $record->user->name ?? 'Unknown';
                        $nameParts = explode(' ', $name);
                        $lastName  = array_pop($nameParts);
                        $firstName = implode('', array_map(
                            fn($p) => ucfirst(strtolower($p)), $nameParts
                        ));
                        $date     = $record->start_date
                            ? \Carbon\Carbon::parse($record->start_date)->format('m.d.y')
                            : now()->format('m.d.y');
                        $fileName = 'TO.' . $lastName . '.' . $firstName . '.' . $date . '.pdf';

                        return response()->streamDownload(
                            function () use ($pdf) {
                                echo $pdf->output();
                            },
                            $fileName
                        );
                    }),

                Tables\Actions\Action::make('cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) =>
                        in_array($record->status, ['SUBMITTED', 'PENDING', 'APPROVED', 'COMPLETED', 'FOR_REVISION']) &&
                        $record->user_id === auth()->id()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Travel Order')
                    ->modalDescription('Are you sure? The TO number will be preserved for audit purposes.')
                    ->form([
                        Forms\Components\Textarea::make('cancel_reason')
                            ->label('Reason for Cancellation')
                            ->placeholder('Please provide a reason for cancelling this travel order...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (TravelOrder $record, array $data) {
                        app(TravelOrderService::class)
                            ->cancelOrder($record, $data['cancel_reason']);

                        Notification::make()
                            ->title('Travel Order Cancelled')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn($record) =>
                        in_array($record->status, ['DRAFT', 'FOR_REVISION']) &&
                        $record->user_id === auth()->id()
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) =>
                        $record->status === 'DRAFT' &&
                        $record->user_id === auth()->id()
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'   => Pages\ListTravelOrders::route('/'),
            'create'  => Pages\CreateTravelOrder::route('/create'),
            'preview' => Pages\PreviewTravelOrder::route('/{record}/preview'),
            'view'    => Pages\ViewTravelOrder::route('/{record}'),
            'edit'    => Pages\EditTravelOrder::route('/{record}/edit'),
        ];
    }
}