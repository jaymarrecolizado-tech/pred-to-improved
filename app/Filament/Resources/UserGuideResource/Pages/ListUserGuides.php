<?php

namespace App\Filament\Resources\UserGuideResource\Pages;

use App\Filament\Resources\UserGuideResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserGuides extends ListRecords
{
    protected static string $resource = UserGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add step'),
        ];
    }

    public function getTitle(): string
    {
        return 'User Guide';
    }

    public function getHeading(): string
    {
        return 'User Guide — follow the steps in order';
    }
}
