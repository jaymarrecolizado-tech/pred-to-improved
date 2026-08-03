<?php
namespace App\Notifications;
use App\Models\TravelOrder;
use App\Models\TravelApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class TravelOrderFollowUp extends Notification
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
        $toCode      = $this->travelOrder->to_code ?? 'Pending';
        $requestor   = $this->travelOrder->user->name ?? 'Unknown';
        $startDate   = optional($this->travelOrder->start_date)->format('M d, Y') ?? 'N/A';
        $endDate     = optional($this->travelOrder->end_date)->format('M d, Y') ?? 'N/A';
        $approvalUrl = url('/DICT/travel-approvals');
        return (new MailMessage)
            ->subject("Follow Up: Travel Order Awaiting Your Approval - {$toCode}")
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line("This is a friendly follow up from **{$requestor}** regarding a travel order that is pending your approval.")
            ->line('---')
            ->line('**Travel Order:** ' . $toCode)
            ->line('**Requested By:** ' . $requestor)
            ->line('**Travel Dates:** ' . $startDate . ' to ' . $endDate)
            ->line('**Purpose:** ' . ($this->travelOrder->purpose ?? 'N/A'))
            ->line('---')
            ->line('Please log in to the system to review and take action at your earliest convenience.')
            ->action('Go to Approval Panel', $approvalUrl)
            ->line('Thank you for your time.');
    }
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code'         => $this->travelOrder->to_code,
            'message'         => 'Follow up: ' . ($this->travelOrder->user->name ?? '') . ' is following up on their travel order.',
            'url'             => '/DICT/travel-approvals',
        ];
    }
}