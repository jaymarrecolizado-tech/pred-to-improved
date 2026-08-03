<?php
namespace App\Notifications;
use App\Models\TravelOrder;
use App\Models\TravelApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TravelOrderApproved extends Notification
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
        $itinerary    = $this->travelOrder->travel_location;
        $origins      = $formatItinerary($itinerary, 'origin');
        $destinations = $formatItinerary($itinerary, 'destination');
        $toCode     = $this->travelOrder->to_code ?? 'Pending';
        $approver   = $this->approval->approver->name ?? 'Unknown';
        $startDate  = optional($this->travelOrder->start_date)->format('M d, Y') ?? 'N/A';
        $endDate    = optional($this->travelOrder->end_date)->format('M d, Y') ?? 'N/A';
        $approvedAt = optional($this->approval->approved_at)->format('M d, Y h:i A') ?? 'N/A';
        $viewUrl    = url("/DICT/travel-orders/{$this->travelOrder->id}");
        return (new MailMessage)
            ->subject('Travel Order Approved - ' . $toCode)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your travel order has been approved by **' . $approver . '** and is now moving to the next step.')
            ->line('---')
            ->line('**Travel Order Details:**')
            ->line('TO Code: **' . $toCode . '**')
            ->line('Travel Dates: **' . $startDate . '** to **' . $endDate . '**')
            ->line('Origin: ' . $origins)
            ->line('Destination: ' . $destinations)
            ->line('Approved By: **' . $approver . '**')
            ->line('Approved At: ' . $approvedAt)
            ->line('---')
            ->line('Your travel order is now in the next approval stage. You will be notified once fully processed.')
            ->action('View Travel Order', $viewUrl)
            ->line('Thank you!');
    }
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code'         => $this->travelOrder->to_code,
            'message'         => 'Travel order approved by ' . ($this->approval->approver->name ?? 'Unknown'),
            'url'             => "/DICT/travel-orders/{$this->travelOrder->id}",
        ];
    }
}