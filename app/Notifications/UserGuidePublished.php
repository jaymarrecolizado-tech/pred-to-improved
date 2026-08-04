<?php

namespace App\Notifications;

use App\Models\UserGuide;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserGuidePublished extends Notification
{
    use Queueable;

    public function __construct(
        public UserGuide $userGuide
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'User Guide updated',
            'message' => $this->userGuide->title,
            'url' => '/DICT/user-guide/' . $this->userGuide->id,
            'icon' => 'heroicon-o-book-open',
        ];
    }
}
