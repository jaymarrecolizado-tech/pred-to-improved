<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelWorkflowResource\Pages;
use App\Models\TravelWorkflow;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class TravelWorkflowResource extends Resource
{
    protected static ?string $model = TravelWorkflow::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([

                        // Primary workflow details — step number, approver assignment, and label
                        Forms\Components\Section::make('Step Configuration')
                            ->columnSpan(2)
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Forms\Components\Hidden::make('user_id')
                                    ->default(fn() => auth()->id())
                                    ->dehydrated(true),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('workflow_step')
                                        ->label('Sequence Number')
                                        ->numeric()
                                        ->prefix('Step')
                                        ->minValue(1)
                                        ->default(1)
                                        ->required(),

                                    Forms\Components\Select::make('approver_id')
                                        ->label('Designated Approver')
                                        ->relationship('approver', 'name')
                                        ->options(
                                            User::whereIn('role', ['admin', 'super_admin', 'hr'])
                                                ->pluck('name', 'id')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ]),

                                Forms\Components\Textarea::make('description')
                                    ->label('Internal Notes')
                                    ->placeholder('Describe the purpose of this approval step...')
                                    ->rows(3),
                            ]),

                        // Workflow state controls — active status and email notification toggle
                        Forms\Components\Section::make('System Status')
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Toggle::make('active')
                                    ->label('Workflow Active')
                                    ->helperText('Enable this sequence')
                                    ->default(true),

                                Forms\Components\Toggle::make('notify_email')
                                    ->label('Email Alerts')
                                    ->helperText('Send automated notifications')
                                    ->default(true),
                            ]),

                        // Approval role flags — determines what action this step performs in the TO routing
                        Forms\Components\Section::make('Role Assignments')
                            ->description('Define the authority granted at this specific step.')
                            ->icon('heroicon-o-shield-check')
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Toggle::make('to_provincial_officer')
                                        ->label('Provincial Officer Initial')
                                        ->helperText('First check by the Provincial Head.'),

                                    Forms\Components\Toggle::make('to_provincial_officer_two')
                                        ->label('Provincial Officer (Optional)')
                                        ->helperText('Second PO check (only if needed).'),

                                    Forms\Components\Toggle::make('to_recommend')
                                        ->label('TOD Chief Signature')
                                        ->helperText('Main signature for Technical operations.'),

                                    Forms\Components\Toggle::make('to_admin_initial')
                                        ->label('AFD Chief Initial')
                                        ->helperText('Review by the Administrative Chief.'),

                                    Forms\Components\Toggle::make('to_admin_recommend')
                                        ->label('AFD Chief Signature')
                                        ->helperText('Main signature for Finance/Admin operations.'),

                                    Forms\Components\Toggle::make('to_ard_initial')
                                        ->label('ARD Initial')
                                        ->helperText('Final Review by the Assistant Regional Director.'),

                                    Forms\Components\Toggle::make('to_approve')
                                        ->label('Regional Director Signature')
                                        ->helperText('Final Authority to Approve Travel Order.'),

                                    // OIC-RD toggle — when enabled, approver signs as OIC Regional Director instead of their actual position
                                    Forms\Components\Toggle::make('to_oic_rd')
                                        ->label('OIC, Regional Director Signature')
                                        ->helperText('OIC - Final Authority to Approve Travel Order.'),

                                    Forms\Components\Toggle::make('to_code_provider')
                                        ->label('TO No. Provider')
                                        ->helperText('HR Assigns the official Travel Order number.'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workflow_step')
                    ->label('Step No.')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approver Name')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => "User: " . ($record->approver->email ?? 'No Email')),

                Tables\Columns\TextColumn::make('roles')
                    ->label('Assigned Responsibilities')
                    ->badge()
                    ->state(function ($record) {
                        $roles = [];
                        if ($record->to_provincial_officer)     $roles[] = 'PO 1';
                        if ($record->to_provincial_officer_two) $roles[] = 'PO 2 (Opt)';
                        if ($record->to_recommend)              $roles[] = 'TOD Chief';
                        if ($record->to_admin_recommend)        $roles[] = 'AFD Chief';
                        if ($record->to_admin_initial)          $roles[] = 'AFD Chief Initial';
                        if ($record->to_ard_initial)            $roles[] = 'ARD Initial';
                        if ($record->to_approve)                $roles[] = 'Regional Director';
                        if ($record->to_oic_rd)                 $roles[] = 'OIC, Reg. Director';
                        if ($record->to_hr_route)               $roles[] = 'HR Route';
                        return $roles;
                    })
                    ->separator(',')
                    ->color('success'),

                Tables\Columns\IconColumn::make('to_approve')
                    ->label('Final Signer')
                    ->boolean()
                    ->trueIcon('heroicon-s-pencil-square')
                    ->falseIcon('heroicon-o-minus')
                    ->color('danger')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('to_oic_rd')
                    ->label('OIC-RD')
                    ->boolean()
                    ->trueIcon('heroicon-s-pencil-square')
                    ->falseIcon('heroicon-o-minus')
                    ->color('warning')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('to_code_provider')
                    ->label('TO No. (HR)')
                    ->boolean()
                    ->trueIcon('heroicon-s-hashtag')
                    ->falseIcon('heroicon-o-minus')
                    ->color('warning')
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('active')
                    ->label('Enabled')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->color('gray'),
            ])
            ->defaultSort('workflow_step')
            ->reorderable('workflow_step');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTravelWorkflows::route('/'),
            'create' => Pages\CreateTravelWorkflow::route('/create'),
            'edit'   => Pages\EditTravelWorkflow::route('/{record}/edit'),
        ];
    }
}