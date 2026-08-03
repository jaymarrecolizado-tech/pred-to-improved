<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelOrder;
use App\Services\TravelOrderService;

class LockExistingPdfs extends Command
{
    protected $signature   = 'pdfs:lock-existing';
    protected $description = 'Generate and store PDFs for all existing completed travel orders';

    public function handle()
    {
        $orders = TravelOrder::whereIn('status', ['COMPLETED', 'CANCELLED'])
            ->whereNull('pdf_path')
            ->with([
                'user',
                'approvals.workflow',
                'approvals.approver.employee',
            ])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No completed travel orders without stored PDFs found.');
            return;
        }

        $this->info("Found {$orders->count()} travel orders to process...");

        $service = app(TravelOrderService::class);
        $success = 0;
        $failed  = 0;

        foreach ($orders as $order) {
            $this->line("Processing: [{$order->to_code}] {$order->user->name}");

            $path = $service->generateAndStorePdf($order);

            if ($path) {
                $success++;
                $this->info("  ✓ Stored: {$path}");
            } else {
                $failed++;
                $this->error("  ✗ Failed for order ID: {$order->id}");
            }
        }

        $this->info("Done — {$success} stored, {$failed} failed.");
    }
}