<?php
namespace App\Notifications;
use App\Models\TravelOrder;
use App\Models\TravelApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TravelOrderRejected extends Notification
{
    use Queueable;
    public function __construct(
        public TravelOrder $travelOrder,
        public TravelApproval $approval
    ) {}
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }
    public function toMail(object $notifiable): MailMessage
    {
        $formatItinerary = function ($locations, $key) {
            if (empty($locations) || !is_array($locations)) return 'N/A';
            $values = array_column($locations, $key);
            return implode(', ', array_filter($values));
        };
        $toCode      = $this->travelOrder->to_code ?? 'Pending';
        $approver    = $this->approval->approver->name ?? 'Unknown Approver';
        $reason      = $this->approval->reject_reason ?? 'No reason provided.';
        $rejectedAt  = optional($this->approval->rejected_at)->format('M d, Y h:i A') ?? 'N/A';
        $startDate   = optional($this->travelOrder->start_date)->format('M d, Y') ?? 'N/A';
        $endDate     = optional($this->travelOrder->end_date)->format('M d, Y') ?? 'N/A';
        $itinerary   = $this->travelOrder->travel_location;
        $origin      = $formatItinerary($itinerary, 'origin');
        $destination = $formatItinerary($itinerary, 'destination');
        $editUrl     = url("/DICT/travel-orders/{$this->travelOrder->id}/edit");
        return (new MailMessage)
            ->error()
            ->subject("Travel Order Rejected - {$toCode}")
            ->greeting("Dear {$notifiable->name},")
            ->line("We regret to inform you that your travel order has been **rejected** by **{$approver}**.")
            ->line('---')
            ->line('**Travel Order Details:**')
            ->line('TO Code: **' . $toCode . '**')
            ->line('Route: From **' . $origin . '** to **' . $destination . '**')
            ->line('Travel Dates: **' . $startDate . '** to **' . $endDate . '**')
            ->line('Rejected At: ' . $rejectedAt)
            ->line('---')
            ->line('**Reason for Rejection:**')
            ->line('> ' . $reason)
            ->line('---')
            ->line('Please review the rejection reason carefully. You may edit and resubmit your travel order if needed.')
            ->action('Edit & Resubmit', $editUrl)
            ->line('Thank you for your understanding.');
    }
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code'         => $this->travelOrder->to_code,
            'status'          => 'rejected',
            'approver'        => $this->approval->approver->name ?? null,
            'message'         => 'Travel order rejected by ' . ($this->approval->approver->name ?? 'Unknown'),
            'url'             => "/DICT/travel-orders/{$this->travelOrder->id}/edit",
        ];
    }
}