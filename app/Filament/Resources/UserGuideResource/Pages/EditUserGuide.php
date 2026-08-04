<?php

namespace App\Filament\Resources\UserGuideResource\Pages;

use App\Filament\Resources\UserGuideResource;
use App\Models\User;
use App\Notifications\UserGuidePublished;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditUserGuide extends EditRecord
{
    protected static string $resource = UserGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if (!$this->record->is_published) {
            return;
        }

        $original = $this->record->getOriginal('is_published');

        if ($original === true || $original === 1) {
            return;
        }

        foreach (User::all() as $user) {
            try {
                $user->notify(new UserGuidePublished($this->record));
            } catch (\Exception $e) {
                Log::error('Failed to send user guide notification to ' . $user->email . ': ' . $e->getMessage());
            }
        }
    }
}
