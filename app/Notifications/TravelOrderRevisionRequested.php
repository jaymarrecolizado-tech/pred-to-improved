<?php
namespace App\Notifications;
use App\Models\TravelOrder;
use App\Models\TravelApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TravelOrderRevisionRequested extends Notification
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
        $toCode    = $this->travelOrder->to_code ?? 'Pending';
        $approver  = $this->approval->approver->name ?? 'An Approver';
        $reason    = $this->approval->revision_reason ?? 'No reason provided.';
        $startDate = optional($this->travelOrder->start_date)->format('M d, Y') ?? 'N/A';
        $endDate   = optional($this->travelOrder->end_date)->format('M d, Y') ?? 'N/A';
        $editUrl   = url("/DICT/travel-orders/{$this->travelOrder->id}/edit");
        return (new MailMessage)
            ->subject("Travel Order Needs Revision - {$toCode}")
            ->greeting("Dear {$notifiable->name},")
            ->line("Your travel order **{$toCode}** has been sent back for revision by **{$approver}**.")
            ->line('---')
            ->line('**Revision Reason:**')
            ->line('> ' . $reason)
            ->line('---')
            ->line('**Travel Dates:** ' . $startDate . ' to ' . $endDate)
            ->line('Please edit your travel order and resubmit. You may update any field including dates, travelers, funding source, and purpose.')
            ->action('Edit Travel Order Now', $editUrl)
            ->line('Thank you.');
    }
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code'         => $this->travelOrder->to_code,
            'message'         => 'Travel order needs revision: ' . ($this->approval->revision_reason ?? ''),
            'url'             => "/DICT/travel-orders/{$this->travelOrder->id}/edit",
        ];
    }
}