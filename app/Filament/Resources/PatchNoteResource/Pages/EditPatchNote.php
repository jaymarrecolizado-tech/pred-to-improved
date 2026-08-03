<?php

namespace App\Filament\Resources\PatchNoteResource\Pages;

use App\Filament\Resources\PatchNoteResource;
use App\Models\User;
use App\Notifications\PatchNotePublished;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatchNote extends EditRecord
{
    protected static string $resource = PatchNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Notify users only on first publish - triggered when is_published changes from false to true
        if (!$this->record->is_published) {
            return;
        }


        $original = $this->record->getOriginal('is_published');

        if ($original === true || $original === 1) {
            return; // Already was published before - don't re-notify
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