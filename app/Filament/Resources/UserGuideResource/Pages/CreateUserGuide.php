<?php

namespace App\Filament\Resources\UserGuideResource\Pages;

use App\Filament\Resources\UserGuideResource;
use App\Models\User;
use App\Notifications\UserGuidePublished;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateUserGuide extends CreateRecord
{
    protected static string $resource = UserGuideResource::class;

    protected function afterCreate(): void
    {
        if (!$this->record->is_published) {
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
