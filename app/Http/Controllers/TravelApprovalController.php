<?php

namespace App\Http\Controllers;

use App\Models\TravelApproval;
use App\Models\TravelOrder;
use App\Services\TravelOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TravelApprovalController extends Controller
{
    public function approve(Request $request, TravelApproval $approval, TravelOrderService $service)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired approval link.');
        }

        if ($approval->status !== 'PENDING') {
            return $this->htmlResponse(
                'Already Processed',
                'This approval has already been processed.',
                'warning'
            );
        }

        try {
            $service->approveStep($approval);
            return $this->htmlResponse(
                'Travel Order Approved',
                'You have successfully approved this travel order.',
                'success'
            );
        } catch (\Exception $e) {
            Log::error('Approval error: ' . $e->getMessage());
            return $this->htmlResponse(
                'Error Occurred',
                'An error occurred while processing the approval. Please try again later.',
                'error'
            );
        }
    }

    public function reject(Request $request, TravelApproval $approval, TravelOrderService $service)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired rejection link.');
        }

        if ($approval->status !== 'PENDING') {
            return $this->htmlResponse(
                'Already Processed',
                'This approval has already been processed.',
                'warning'
            );
        }

        $reason = $request->query('reason', 'Rejected via email link');

        try {
            $service->rejectStep($approval, $reason);
            return $this->htmlResponse(
                'Travel Order Rejected',
                'You have successfully rejected this travel order.',
                'danger'
            );
        } catch (\Exception $e) {
            Log::error('Rejection error: ' . $e->getMessage());
            return $this->htmlResponse(
                'Error Occurred',
                'An error occurred while processing the rejection. Please try again later.',
                'error'
            );
        }
    }

    public function rejectForm(Request $request, TravelApproval $approval)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        if ($approval->status !== 'PENDING') {
            return $this->htmlResponse(
                'Already Processed',
                'This approval has already been processed.',
                'warning'
            );
        }

        return view('approvals.reject-form', [
            'approval' => $approval,
        ]);
    }

    public function rejectSubmit(Request $request, TravelApproval $approval, TravelOrderService $service)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        if ($approval->status !== 'PENDING') {
            return $this->htmlResponse(
                'Already Processed',
                'This approval has already been processed.',
                'warning'
            );
        }

        try {
            $service->rejectStep($approval, $request->reason);
            return $this->htmlResponse(
                'Travel Order Rejected',
                'You have successfully rejected this travel order.',
                'danger'
            );
        } catch (\Exception $e) {
            Log::error('Rejection error: ' . $e->getMessage());
            return $this->htmlResponse(
                'Error Occurred',
                'An error occurred while processing the rejection. Please try again later.',
                'error'
            );
        }
    }

    public function previewPdf(TravelOrder $order)
    {
        $user       = auth()->user();
        $isOwner    = $order->user_id === $user->id;
        $isAdmin    = $user->isAdmin();
        $isTraveler = collect($order->travelers ?? [])
            ->contains(fn($t) => ($t['name'] ?? '') === $user->name);

        if (!$isOwner && !$isAdmin && !$isTraveler) {
            abort(403, 'You do not have permission to view this travel order.');
        }

        $order->load([
            'user',
            'approvals.workflow',
            'approvals.approver.employee',
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.TravelOrderCompleted',
            ['travelOrder' => $order]
        )->setPaper('a4', 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="travel-order-' . ($order->to_code ?? $order->id) . '.pdf"',
        ]);
    }

    protected function htmlResponse(string $title, string $message, string $type = 'info')
    {
        $colors = [
            'success' => '#16a34a',
            'danger'  => '#dc2626',
            'warning' => '#facc15',
            'error'   => '#ef4444',
            'info'    => '#3b82f6',
        ];

        $color = $colors[$type] ?? $colors['info'];

        return response()->make("
            <html>
                <head><title>{$title}</title></head>
                <body style='font-family: Arial, sans-serif; background: #f9fafb; text-align: center; padding: 60px;'>
                    <div style='display: inline-block; padding: 30px 50px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                        <h2 style='color: {$color}; margin-bottom: 10px;'>{$title}</h2>
                        <p style='font-size: 16px; color: #374151;'>{$message}</p>
                        <a href='" . url('/DICT/travel-approvals') . "' style='
                            display: inline-block;
                            margin-top: 20px;
                            background-color: {$color};
                            color: white;
                            padding: 10px 20px;
                            border-radius: 6px;
                            text-decoration: none;'>
                            Go to Dashboard
                        </a>
                    </div>
                </body>
            </html>
        ");
    }
}
