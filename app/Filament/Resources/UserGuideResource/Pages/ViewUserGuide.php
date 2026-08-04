<?php

namespace App\Filament\Resources\UserGuideResource\Pages;

use App\Filament\Resources\UserGuideResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserGuide extends ViewRecord
{
    protected static string $resource = UserGuideResource::class;

    public function getTitle(): string
    {
        return $this->record->stepHeading();
    }

    public function getHeading(): string
    {
        return $this->record->stepHeading();
    }

    protected function getHeaderActions(): array
    {
        $current = (int) $this->record->sort_order;

        $previous = \App\Models\UserGuide::query()
            ->when(!auth()->user()?->isSuperAdmin(), fn ($q) => $q->published())
            ->where('sort_order', '<', $current)
            ->orderByDesc('sort_order')
            ->first();

        $next = \App\Models\UserGuide::query()
            ->when(!auth()->user()?->isSuperAdmin(), fn ($q) => $q->published())
            ->where('sort_order', '>', $current)
            ->orderBy('sort_order')
            ->first();

        return [
            Actions\Action::make('previous')
                ->label('Previous: ' . ($previous?->stepLabel() ?? ''))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->visible(fn () => (bool) $previous)
                ->url(fn () => UserGuideResource::getUrl('view', ['record' => $previous])),

            Actions\Action::make('next')
                ->label('Next: ' . ($next?->stepLabel() ?? ''))
                ->icon('heroicon-o-arrow-right')
                ->color('primary')
                ->visible(fn () => (bool) $next)
                ->url(fn () => UserGuideResource::getUrl('view', ['record' => $next])),

            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin()),

            Actions\Action::make('back')
                ->label('All steps')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(UserGuideResource::getUrl('index')),
        ];
    }
}
