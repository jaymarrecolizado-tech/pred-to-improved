<?php

namespace App\Filament\Pages;

use App\Models\TravelOrder;
use App\Filament\Resources\TravelOrderResource;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Calendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-m-calendar-days';
    protected static ?string $navigationGroup = 'Travel Management';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.calendar';

    public function getViewData(): array
    {
        $user  = Auth::user();
        $query = TravelOrder::with('user');

        if (!$user->isAdmin()) {
            $userName = $user->name;

            $query->where(function ($q) use ($user, $userName) {
                $q->where('user_id', $user->id);

                if ($userName) {
                    $q->orWhereRaw(
                        "JSON_CONTAINS(travelers, JSON_OBJECT('name', ?), '$')",
                        [$userName]
                    );
                }
            });
        }

        $orders = $query->get();

        $events = $orders->map(function ($travel) {

            $color = match ($travel->status) {
                'APPROVED'     => '#22c55e',
                'COMPLETED'    => '#16a34a',
                'PENDING',
                'SUBMITTED'    => '#f59e0b',
                'REJECTED'     => '#ef4444',
                'CANCELLED'    => '#94a3b8',
                'FOR_REVISION' => '#3b82f6',
                default        => null,
            };

            if ($color === null) return null;

            // Pass raw to_code value — null for orders without an assigned code yet.
            // The calendar JS renders the TO code if available, otherwise falls back to showing the status.
            $toCode    = $travel->to_code;
            $requestor = $travel->user->name ?? 'Unknown';

            $locations   = $travel->travel_location ?? [];
            $origin      = collect($locations)->pluck('origin')->filter()->implode(', ') ?: '—';
            $destination = collect($locations)->pluck('destination')->filter()->implode(', ') ?: '—';

            $travelers = collect($travel->travelers ?? [])
                ->pluck('name')
                ->filter()
                ->implode(', ') ?: $requestor;

            $funding = is_array($travel->travel_sources)
                ? implode(', ', array_filter($travel->travel_sources))
                : ($travel->travel_sources ?? '—');

            $vehicle = $travel->formattedVehicles() ?: '—';

            $endDate = $travel->end_date
                ? $travel->end_date->copy()->addDay()->format('Y-m-d')
                : null;

            // Calendar event title uses TO code for completed orders, status label for pending/draft ones
            $eventTitle = $toCode
                ? $toCode . ' · ' . $requestor
                : $travel->status . ' · ' . $requestor;

            return [
                'id'              => $travel->id,
                'title'           => $eventTitle,
                'start'           => $travel->start_date->format('Y-m-d'),
                'end'             => $endDate,
                'url'             => TravelOrderResource::getUrl('view', ['record' => $travel->id]),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'to_code'     => $toCode,      // null if not assigned
                    'status'      => $travel->status,
                    'requestor'   => $requestor,
                    'travelers'   => $travelers,
                    'start_date'  => $travel->start_date->format('M d, Y'),
                    'end_date'    => optional($travel->end_date)->format('M d, Y') ?? '—',
                    'origin'      => $origin,
                    'destination' => $destination,
                    'purpose'     => $travel->purpose ?? '—',
                    'vehicle'     => $vehicle,
                    'funding'     => $funding,
                ],
            ];

        })->filter()->values()->toArray();

        $summary = [
            'total'        => $orders->count(),
            'pending'      => $orders->whereIn('status', ['PENDING', 'SUBMITTED'])->count(),
            'approved'     => $orders->where('status', 'APPROVED')->count(),
            'completed'    => $orders->where('status', 'COMPLETED')->count(),
            'rejected'     => $orders->where('status', 'REJECTED')->count(),
            'cancelled'    => $orders->where('status', 'CANCELLED')->count(),
            'for_revision' => $orders->where('status', 'FOR_REVISION')->count(),
        ];

        return compact('events', 'summary');
    }
}