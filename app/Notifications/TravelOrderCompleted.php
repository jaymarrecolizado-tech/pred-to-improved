<?php
namespace App\Notifications;
use App\Models\TravelOrder;
use App\Models\TravelApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
class TravelOrderCompleted extends Notification
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
        $toCode    = $this->travelOrder->to_code ?? 'Pending';
        $startDate = optional($this->travelOrder->start_date)->format('M d, Y') ?? 'N/A';
        $endDate   = optional($this->travelOrder->end_date)->format('M d, Y') ?? 'N/A';
        $itinerary   = $this->travelOrder->travel_location;
        $origin      = $formatItinerary($itinerary, 'origin');
        $destination = $formatItinerary($itinerary, 'destination');
        $viewUrl  = url("/DICT/travel-orders/{$this->travelOrder->id}");
        $filename = 'TravelOrder_' . str_replace('-', '_', $toCode) . '.pdf';
        $mail = (new MailMessage)
            ->success()
            ->subject('Travel Order Completed - ' . $toCode)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Great news! Your travel order has completed the full approval workflow and has been assigned an official Travel Order number.')
            ->line('---')
            ->line('**Travel Order Details:**')
            ->line('Official TO Code: **' . $toCode . '**')
            ->line('Route: From **' . $origin . '** to **' . $destination . '**')
            ->line('Travel Dates: **' . $startDate . '** to **' . $endDate . '**')
            ->line('---')
            ->line('All required signatures including the Regional Director and HR are now complete.')
            ->line('Please print the attached PDF and bring it for your travel records.')
            ->action('View Travel Order', $viewUrl)
            ->line('Safe travels!');
        try {
            $pdf = Pdf::loadView('pdf.TravelOrderCompleted', [
                'travelOrder' => $this->travelOrder,
            ])->setPaper('a4', 'portrait')->output();
            $mail->attachData(
                $pdf,
                $filename,
                ['mime' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('TravelOrderCompleted PDF generation failed: ' . $e->getMessage(), [
                'travel_order_id' => $this->travelOrder->id,
            ]);
            $mail->line('*(Note: PDF attachment could not be generated. Please download it from the dashboard.)*');
        }
        return $mail;
    }
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'to_code'         => $this->travelOrder->to_code,
            'message'         => 'Your travel order ' . ($this->travelOrder->to_code ?? '') . ' has been completed successfully.',
            'url'             => "/DICT/travel-orders/{$this->travelOrder->id}",
        ];
    }
}