<?php

namespace App\Filament\Resources\PatchNoteResource\Pages;

use App\Filament\Resources\PatchNoteResource;
use App\Models\User;
use App\Notifications\PatchNotePublished;
use Filament\Resources\Pages\CreateRecord;

class CreatePatchNote extends CreateRecord
{
    protected static string $resource = PatchNoteResource::class;

    protected function afterCreate(): void
    {
        // Only notify if the patch note is published
        if (!$this->record->is_published) {
            return;
        }

        $users = User::all();

        foreach ($users as $user) {
            try {
                $user->notify(new PatchNotePublished($this->record));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error(
                    'Failed to send patch note notification to ' . $user->email . ': ' . $e->getMessage()
                );
            }
        }
    }
}