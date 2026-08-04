<?php
namespace App\Notifications;
use App\Models\TravelOrder;
use App\Models\TravelApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TravelOrderSubmitted extends Notification
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
        $fundingSource = 'Not Specified';
        if (!empty($this->travelOrder->travel_sources)) {
            if (is_array($this->travelOrder->travel_sources)) {
                $fundingSource = implode(', ', array_filter($this->travelOrder->travel_sources));
            } else {
                $fundingSource = $this->travelOrder->travel_sources;
            }
        } elseif ($this->travelOrder->travelSource) {
            $fundingSource = $this->travelOrder->travelSource->name;
        }
        $itinerary   = $this->travelOrder->travel_location;
        $destination = $formatItinerary($itinerary, 'destination');
        $approvalUrl = url('/DICT/travel-approvals');
        return (new MailMessage)
            ->subject('Travel Order Pending Your Approval - ' . ($this->travelOrder->to_code ?? 'Pending'))
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('A travel order has been submitted and requires your review and approval.')
            ->line('**TRAVEL ORDER DETAILS:**')
            ->line('Travel Order Number: **' . ($this->travelOrder->to_code ?? 'Pending') . '**')
            ->line('Requested By: ' . $this->travelOrder->user->name)
            ->line('Travel Period: ' . $this->travelOrder->start_date->format('F j, Y') . ' to ' . $this->travelOrder->end_date->format('F j, Y'))
            ->line('Destination: ' . $destination)
            ->line('Funding Source: ' . $fundingSource)
            ->line('Selected Vehicle: ' . ($this->travelOrder->formattedVehicles() ?: 'Pending Assignment'))
            ->line('Purpose: ' . $this->travelOrder->purpose)
            ->line('Remarks: ' . $this->travelOrder->remarks)
            ->when(!empty($this->travelOrder->travelers), function (MailMessage $message) {
                $travelersList = '';
                foreach ($this->travelOrder->travelers as $traveler) {
                    $name = is_array($traveler) ? ($traveler['name'] ?? 'Unknown') : $traveler;
                    $travelersList .= "• {$name}\n";
                }
                return $message->line("**Traveler/s:**\n" . $travelersList);
            })
            ->line('')
            ->line('Please log in to the system to review and take action — approve, reject, or request revision.')
            ->action('Go to Approval Panel', $approvalUrl)
            ->line('Thank you for your timely review.');
    }
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code'         => $this->travelOrder->to_code,
            'message'         => 'Travel order requires your approval',
            'url'             => "/DICT/travel-approvals",
        ];
    }
}