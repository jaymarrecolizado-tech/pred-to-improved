<?php

namespace App\Filament\Resources\PatchNoteResource\Pages;

use App\Filament\Resources\PatchNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatchNotes extends ListRecords
{
    protected static string $resource = PatchNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}