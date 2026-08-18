<?php

namespace App\Services;

use App\Models\TravelOrder;
use App\Models\TravelApproval;
use App\Models\TravelWorkflow;
use App\Models\User;
use App\Notifications\TravelOrderSubmitted;
use App\Notifications\TravelOrderApproved;
use App\Notifications\TravelOrderRejected;
use App\Notifications\TravelOrderCompleted;
use App\Notifications\TravelOrderParticipantNotified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class TravelOrderService
{
    private function generateToCode(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');

        $last = DB::table('travel_orders')
            ->where('to_code', 'REGEXP', '^' . $year . '-[0-9]{2}-[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING_INDEX(to_code, "-", -1) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->first();

        $lastNumber = (int) config('travel.to_code_sequence_start', 0);

        if ($last) {
            $parts    = explode('-', $last->to_code);
            $lastYear = $parts[0];
            if ($lastYear === $year) {
                $lastNumber = (int) end($parts);
            }
        }

        return sprintf('%s-%s-%03d', $year, $month, $lastNumber + 1);
    }

    /**
     * Capture snapshot of approver data at time of approval
     * Covers all role types: PO, TOD Chief, AFD Chief, AFD Initial,
     * ARD Initial, OIC RD, Regional Director, HR
     */
    private function captureApproverSnapshot(TravelApproval $approval): array
    {
        $approver  = $approval->approver;
        $employee  = $approver?->employee;
        $workflow  = \App\Models\TravelWorkflow::find($approval->workflow_step);

        //  Map workflow role flags to their official position titles for the PDF signatory block
        $position = null;

        if ($workflow) {
            if ($workflow->to_oic_rd) {
                $position = 'OIC, Regional Director';
            } elseif ($workflow->to_approve) {
                $position = $employee?->position ?? 'Regional Director';
            } elseif ($workflow->to_admin_recommend) {
                $position = $employee?->position ?? 'Chief, Admin. and Finance Division';
            } elseif ($workflow->to_recommend) {
                $position = $employee?->position ?? 'Chief, Technical Operations Division';
            } elseif ($workflow->to_admin_initial) {
                $position = $employee?->position ?? 'Chief, Admin. and Finance Division';
            } elseif ($workflow->to_ard_initial) {
                $position = $employee?->position ?? 'Assistant Regional Director';
            } elseif ($workflow->to_provincial_officer || $workflow->to_provincial_officer_two) {
                $position = $employee?->position ?? 'Provincial Officer';
            } elseif ($workflow->to_code_provider) {
                $position = $employee?->position ?? 'HR Officer';
            } else {
                $position = $employee?->position;
            }
        } else {
            $position = $employee?->position;
        }

        return [
            'approver_name_snapshot'      => $approver?->name,
            'approver_position_snapshot'  => $position,
            'approver_signature_snapshot' => $approver?->signature,
        ];
    }

    /**
     * Generate and store PDF for a completed travel order
     */
    public function generateAndStorePdf(TravelOrder $travelOrder): ?string
    {
        try {
            $travelOrder->load([
                'user',
                'approvals.workflow',
                'approvals.approver.employee',
            ]);

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
                ? $travelOrder->start_date->format('m.d.y')
                : now()->format('m.d.y');

            $fileName = 'TO.' . $lastName . '.' . $firstName . '.' . $date . '.pdf';
            $path     = 'travel-orders/pdfs/' . $fileName;

            Storage::disk('public')->put($path, $pdf->output());

            $travelOrder->update(['pdf_path' => $path]);

            return $path;

        } catch (\Exception $e) {
            Log::error('Failed to generate and store PDF: ' . $e->getMessage(), [
                'travel_order_id' => $travelOrder->id,
            ]);
            return null;
        }
    }

    public function createApprovals(TravelOrder $travelOrder): void
    {
        $workflowSteps = $travelOrder->workflow_steps ?? [];

        if (empty($workflowSteps)) {
            throw new \Exception('No workflow steps selected');
        }

        $workflows = TravelWorkflow::whereIn('id', $workflowSteps)
            ->where('user_id', $travelOrder->user_id)
            ->active()
            ->ordered()
            ->get();

        if ($workflows->isEmpty()) {
            throw new \Exception('No valid workflow steps found');
        }

        $firstWorkflow = $workflows->first();

        $firstApproval = TravelApproval::create([
            'travel_order_id' => $travelOrder->id,
            'user_id'         => $travelOrder->user_id,
            'approver_id'     => $firstWorkflow->approver_id,
            'workflow_step'   => $firstWorkflow->id,
            'status'          => 'PENDING',
            'to_code'         => null,
        ]);

        $travelOrder->update(['status' => 'PENDING']);

        if ($firstWorkflow->notify_email) {
            try {
                $firstApproval->approver->notify(
                    new TravelOrderSubmitted($travelOrder, $firstApproval)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send TravelOrderSubmitted email: ' . $e->getMessage(), [
                    'travel_order_id' => $travelOrder->id,
                    'approval_id'     => $firstApproval->id,
                ]);
            }
        }

        try {
            Notification::make()
                ->title('New Travel Order Pending Approval')
                ->warning()
                ->icon('heroicon-o-document-text')
                ->body("A new request from {$travelOrder->user->name} requires your review.")
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(fn() => "/DICT/travel-approvals"),
                ])
                ->sendToDatabase($firstApproval->approver);
        } catch (\Exception $e) {
            Log::error('Failed to send bell notification: ' . $e->getMessage());
        }

        foreach ($travelOrder->participantUsers() as $participant) {
            try {
                $participant->notify(new TravelOrderParticipantNotified($travelOrder));
            } catch (\Exception $e) {
                Log::error('Failed to send TravelOrderParticipantNotified email: ' . $e->getMessage(), [
                    'travel_order_id' => $travelOrder->id,
                    'participant_id'  => $participant->id,
                ]);
            }
        }
    }

    public function approveStep(TravelApproval $approval): void
    {
        $notifyNext     = null;
        $notifyUser     = null;
        $notifyComplete = false;

        DB::transaction(function () use ($approval, &$notifyNext, &$notifyUser, &$notifyComplete) {

            $travelOrder = $approval->travelOrder;
            $workflow    = TravelWorkflow::find($approval->workflow_step);

            if ($workflow && $workflow->to_code_provider && !$travelOrder->to_code) {
                $toCode = $this->generateToCode();
                $travelOrder->update(['to_code' => $toCode]);
                $approval->update(['to_code' => $toCode]);
            }

            // Capture snapshot of approver at time of approval
            $snapshot = $this->captureApproverSnapshot($approval);

            $approval->update(array_merge([
                'status'      => 'APPROVED',
                'approved_at' => now(),
                'to_code'     => $travelOrder->fresh()->to_code,
            ], $snapshot));

            $selectedWorkflowIds = $travelOrder->workflow_steps ?? [];

            $workflows = TravelWorkflow::whereIn('id', $selectedWorkflowIds)
                ->where('user_id', $travelOrder->user_id)
                ->active()
                ->ordered()
                ->get();

            $currentIndex = $workflows->search(
                fn($w) => $w->id === $approval->workflow_step
            );

            if ($currentIndex !== false && $currentIndex < $workflows->count() - 1) {

                $nextWorkflow = $workflows[$currentIndex + 1];

                $nextApproval = TravelApproval::create([
                    'travel_order_id' => $travelOrder->id,
                    'user_id'         => $travelOrder->user_id,
                    'approver_id'     => $nextWorkflow->approver_id,
                    'workflow_step'   => $nextWorkflow->id,
                    'status'          => 'PENDING',
                    'to_code'         => $travelOrder->fresh()->to_code,
                ]);

                $notifyNext = [
                    'approval'     => $nextApproval,
                    'workflow'     => $nextWorkflow,
                    'travelOrder'  => $travelOrder,
                    'prevApproval' => $approval,
                ];

                $notifyUser = [
                    'travelOrder' => $travelOrder,
                    'approval'    => $approval,
                ];

            } else {

                $travelOrder->update([
                    'status'       => 'COMPLETED',
                    'completed_at' => now(),
                ]);

                $notifyComplete = true;

                $notifyUser = [
                    'travelOrder' => $travelOrder,
                    'approval'    => $approval,
                ];
            }
        });

        // Generate and store PDF immediately after completion
        if ($notifyComplete && $notifyUser) {
            $travelOrder = $notifyUser['travelOrder']->fresh();
            $approval    = $notifyUser['approval'];

            // Generate and lock the PDF immediately after completion — snapshots approver data before any future changes
            $this->generateAndStorePdf($travelOrder);

            try {
                $travelOrder->user->notify(
                    new TravelOrderCompleted($travelOrder, $approval)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send TravelOrderCompleted email: ' . $e->getMessage(), [
                    'travel_order_id' => $travelOrder->id,
                ]);
            }

            foreach ($travelOrder->participantUsers() as $participant) {
                try {
                    $participant->notify(
                        new TravelOrderCompleted($travelOrder, $approval, forParticipant: true)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send TravelOrderCompleted email to participant: ' . $e->getMessage(), [
                        'travel_order_id' => $travelOrder->id,
                        'participant_id'  => $participant->id,
                    ]);
                }
            }

            try {
                Notification::make()
                    ->title('Travel Order Completed')
                    ->success()
                    ->icon('heroicon-o-check-badge')
                    ->body("Your Travel Order #{$travelOrder->to_code} is ready. A PDF copy has been sent to your email.")
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn() => "/DICT/travel-orders/{$travelOrder->id}"),
                    ])
                    ->sendToDatabase($travelOrder->user);
            } catch (\Exception $e) {
                Log::error('Failed to send completion bell notification: ' . $e->getMessage());
            }
        }

        if ($notifyNext) {
            $nextApproval = $notifyNext['approval'];
            $nextWorkflow = $notifyNext['workflow'];
            $travelOrder  = $notifyNext['travelOrder'];
            $prevApproval = $notifyNext['prevApproval'];

            if ($nextWorkflow->notify_email) {
                try {
                    $nextApproval->approver->notify(
                        new TravelOrderSubmitted($travelOrder, $nextApproval)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send next approver email: ' . $e->getMessage(), [
                        'travel_order_id' => $travelOrder->id,
                    ]);
                }
            }

            try {
                $travelOrder->user->notify(
                    new TravelOrderApproved($travelOrder, $prevApproval)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send TravelOrderApproved email: ' . $e->getMessage());
            }

            try {
                Notification::make()
                    ->title('Travel Order Pending Your Approval')
                    ->warning()
                    ->icon('heroicon-o-clock')
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn() => "/DICT/travel-approvals"),
                    ])
                    ->sendToDatabase($nextApproval->approver);

                Notification::make()
                    ->title('Travel Order Updated')
                    ->info()
                    ->icon('heroicon-o-check-circle')
                    ->body("Approved by {$prevApproval->approver->name}. Moving to next step.")
                    ->sendToDatabase($travelOrder->user);
            } catch (\Exception $e) {
                Log::error('Failed to send bell notification: ' . $e->getMessage());
            }
        }
    }

    public function rejectStep(TravelApproval $approval, string $reason): void
    {
        DB::transaction(function () use ($approval, $reason) {

            $approval->update([
                'status'        => 'REJECTED',
                'rejected_at'   => now(),
                'reject_reason' => $reason,
            ]);

            $travelOrder = $approval->travelOrder;

            $travelOrder->update([
                'status'        => 'REJECTED',
                'rejected_at'   => now(),
                'reject_reason' => $reason,
            ]);

            $travelOrder->approvals()
                ->where('status', 'PENDING')
                ->update([
                    'status'      => 'REJECTED',
                    'rejected_at' => now(),
                ]);
        });

        $travelOrder = $approval->travelOrder->fresh();

        try {
            $travelOrder->user->notify(
                new TravelOrderRejected($travelOrder, $approval)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send TravelOrderRejected email: ' . $e->getMessage());
        }

        try {
            Notification::make()
                ->title('Travel Order Rejected')
                ->danger()
                ->icon('heroicon-o-x-circle')
                ->body("Reason: {$reason}")
                ->actions([
                    Action::make('Review & Edit')
                        ->button()
                        ->url(fn() => "/DICT/travel-orders/{$travelOrder->id}/edit"),
                ])
                ->sendToDatabase($travelOrder->user);
        } catch (\Exception $e) {
            Log::error('Failed to send rejection bell notification: ' . $e->getMessage());
        }
    }

    public function cancelOrder(TravelOrder $travelOrder, string $reason): void
    {
        DB::transaction(function () use ($travelOrder, $reason) {

            $currentApproval = $travelOrder->approvals()
                ->where('status', 'PENDING')
                ->orderBy('id')
                ->first();

            $travelOrder->update([
                'status'        => 'CANCELLED',
                'cancel_reason' => $reason,
                'cancelled_at'  => now(),
            ]);

            $travelOrder->approvals()
                ->whereIn('status', ['PENDING', 'FOR_REVISION'])
                ->update([
                    'status'      => 'CANCELLED',
                    'rejected_at' => now(),
                ]);

            if ($currentApproval) {
                $this->_cancelNotifyApproval = $currentApproval;
            }
        });

        $travelOrder = $travelOrder->fresh();

        if (isset($this->_cancelNotifyApproval)) {
            $currentApproval = $this->_cancelNotifyApproval;
            unset($this->_cancelNotifyApproval);

            try {
                Notification::make()
                    ->title('Travel Order Cancelled')
                    ->warning()
                    ->icon('heroicon-o-x-circle')
                    ->body("Travel order from {$travelOrder->user->name} has been cancelled by the requestor.")
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn() => "/DICT/travel-orders/{$travelOrder->id}"),
                    ])
                    ->sendToDatabase($currentApproval->approver);
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation bell notification: ' . $e->getMessage());
            }
        }
    }

    public function requestRevision(TravelApproval $approval, string $reason): void
    {
        DB::transaction(function () use ($approval, $reason) {

            $approval->update([
                'status'          => 'FOR_REVISION',
                'revision_reason' => $reason,
                'revised_at'      => now(),
            ]);

            $approval->travelOrder->update([
                'status'          => 'FOR_REVISION',
                'revision_reason' => $reason,
                'revised_at'      => now(),
            ]);
        });

        $travelOrder = $approval->travelOrder->fresh();

        try {
            Notification::make()
                ->title('Travel Order Needs Revision')
                ->warning()
                ->icon('heroicon-o-pencil-square')
                ->body("Revision requested by {$approval->approver->name}: {$reason}")
                ->actions([
                    Action::make('Edit Now')
                        ->button()
                        ->url(fn() => "/DICT/travel-orders/{$travelOrder->id}/edit"),
                ])
                ->sendToDatabase($travelOrder->user);
        } catch (\Exception $e) {
            Log::error('Failed to send revision bell notification: ' . $e->getMessage());
        }

        try {
            $travelOrder->user->notify(
                new \App\Notifications\TravelOrderRevisionRequested($travelOrder, $approval)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send revision email: ' . $e->getMessage());
        }
    }

    public function resubmitAfterRevision(TravelOrder $travelOrder): void
    {
        DB::transaction(function () use ($travelOrder) {

            $revisionApproval = $travelOrder->approvals()
                ->where('status', 'FOR_REVISION')
                ->orderBy('id')
                ->first();

            if (!$revisionApproval) {
                throw new \Exception('No revision step found.');
            }

            $revisionApproval->update([
                'status'     => 'PENDING',
                'revised_at' => now(),
            ]);

            $travelOrder->update([
                'status'     => 'PENDING',
                'revised_at' => now(),
            ]);

            $this->_resubmitApproval = $revisionApproval;
        });

        $travelOrder = $travelOrder->fresh();

        if (isset($this->_resubmitApproval)) {
            $revisionApproval = $this->_resubmitApproval;
            unset($this->_resubmitApproval);

            try {
                $revisionApproval->approver->notify(
                    new TravelOrderSubmitted($travelOrder, $revisionApproval)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send resubmit email: ' . $e->getMessage());
            }

            try {
                Notification::make()
                    ->title('Revised Travel Order Ready for Review')
                    ->info()
                    ->icon('heroicon-o-document-check')
                    ->body("{$travelOrder->user->name} has revised the travel order and resubmitted for your review.")
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn() => "/DICT/travel-approvals"),
                    ])
                    ->sendToDatabase($revisionApproval->approver);
            } catch (\Exception $e) {
                Log::error('Failed to send resubmit bell notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reassign the current PENDING approval step to a different user.
     * Does not change already-approved stages.
     */
    public function reassignPendingApprover(TravelOrder $travelOrder, int $newApproverId): TravelApproval
    {
        $approval = $travelOrder->getCurrentApprovalStep();

        if (!$approval) {
            throw new \RuntimeException('There is no pending approval step to reassign.');
        }

        if ((int) $approval->approver_id === $newApproverId) {
            return $approval;
        }

        $newApprover = User::findOrFail($newApproverId);

        $approval->update([
            'approver_id' => $newApprover->id,
            'approver_name_snapshot' => null,
            'approver_position_snapshot' => null,
            'approver_signature_snapshot' => null,
        ]);

        $approval->refresh()->load('approver');
        $travelOrder->loadMissing('user');

        try {
            $approval->approver->notify(
                new TravelOrderSubmitted($travelOrder, $approval)
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify reassigned approver: ' . $e->getMessage(), [
                'travel_order_id' => $travelOrder->id,
                'approval_id' => $approval->id,
                'approver_id' => $newApprover->id,
            ]);
        }

        try {
            Notification::make()
                ->title('Travel Order Assigned to You')
                ->warning()
                ->icon('heroicon-o-document-text')
                ->body("A travel order from {$travelOrder->user->name} requires your review.")
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(fn () => '/DICT/travel-approvals'),
                ])
                ->sendToDatabase($approval->approver);
        } catch (\Exception $e) {
            Log::error('Failed to send reassignment bell notification: ' . $e->getMessage());
        }

        return $approval;
    }
}