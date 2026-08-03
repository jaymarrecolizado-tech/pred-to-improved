<?php

namespace App\Notifications;

use App\Models\PatchNote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PatchNotePublished extends Notification
{
    use Queueable;

    public function __construct(
        public PatchNote $patchNote
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New Update: ' . $this->patchNote->version,
            'message' => $this->patchNote->title,
            'url'     => '/DICT/patch-notes',
            'icon'    => 'heroicon-o-rectangle-stack',
        ];
    }
}