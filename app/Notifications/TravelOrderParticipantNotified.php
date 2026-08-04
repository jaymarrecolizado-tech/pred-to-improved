<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderParticipantNotified extends Notification
{
    use Queueable;

    public function __construct(
        public TravelOrder $travelOrder
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formatItinerary = function ($locations, $key) {
            if (empty($locations) || !is_array($locations)) {
                return 'N/A';
            }

            return implode(', ', array_filter(array_column($locations, $key)));
        };

        $toCode = $this->travelOrder->to_code ?? 'Pending';
        $itinerary = $this->travelOrder->travel_location;
        $destination = $formatItinerary($itinerary, 'destination');
        $viewUrl = url("/DICT/travel-orders/{$this->travelOrder->id}");

        return (new MailMessage)
            ->subject('Included in Travel Order - ' . $toCode)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have been included as a traveler on a travel order submitted in the DICT Region 2 Travel Order System.')
            ->line('**TRAVEL ORDER DETAILS:**')
            ->line('Travel Order Number: **' . $toCode . '**')
            ->line('Requested By: ' . ($this->travelOrder->user->name ?? 'N/A'))
            ->line('Travel Period: ' . optional($this->travelOrder->start_date)->format('F j, Y') . ' to ' . optional($this->travelOrder->end_date)->format('F j, Y'))
            ->line('Destination: ' . $destination)
            ->line('Purpose: ' . ($this->travelOrder->purpose ?? 'N/A'))
            ->line('')
            ->line('This notice is for your information only. Approvers are notified separately for signature routing. You will receive another email when this travel order is fully completed, including the official Travel Order PDF. You will not be emailed for each intermediate approval step.')
            ->action('View Travel Order', $viewUrl)
            ->line('Thank you.');
    }

    public function toArray(object $notifiable): array
    {
        $toCode = $this->travelOrder->to_code ?? '(pending code)';

        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code' => $this->travelOrder->to_code,
            'message' => 'You were included as a traveler on travel order ' . $toCode . '. You will get another email when it is fully completed (with the official PDF).',
            'url' => "/DICT/travel-orders/{$this->travelOrder->id}",
        ];
    }
}
