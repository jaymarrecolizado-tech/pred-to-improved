<?php

namespace App\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DatabaseNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;

    public function __construct(string $title, string $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        // Delivers this notification via Filament's database bell notification panel
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // Formats the notification payload for Filament's database notification component
        return FilamentNotification::make()
            ->title($this->title)
            ->body($this->message)
            ->icon('heroicon-o-bell')
            ->getDatabaseMessage();
    }
}
