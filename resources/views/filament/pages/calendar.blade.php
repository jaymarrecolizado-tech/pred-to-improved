<x-filament-panels::page>

    {{-- Summary Bar --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-6 py-3 flex items-center gap-2">
            <span class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ $summary['total'] }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">Total</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2">
            <span class="text-lg font-bold text-amber-500">{{ $summary['pending'] }}</span>
            <span class="text-xs text-amber-500">Pending</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2">
            <span class="text-lg font-bold text-green-500">{{ $summary['approved'] }}</span>
            <span class="text-xs text-green-500">Approved</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2">
            <span class="text-lg font-bold text-green-700">{{ $summary['completed'] }}</span>
            <span class="text-xs text-green-700">Completed</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2">
            <span class="text-lg font-bold text-red-500">{{ $summary['rejected'] }}</span>
            <span class="text-xs text-red-500">Rejected</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2">
            <span class="text-lg font-bold text-slate-400">{{ $summary['cancelled'] }}</span>
            <span class="text-xs text-slate-400">Cancelled</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2">
            <span class="text-lg font-bold text-blue-500">{{ $summary['for_revision'] }}</span>
            <span class="text-xs text-blue-500">For Revision</span>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 text-xs text-gray-600 dark:text-gray-400">
        <span class="font-medium">Legend:</span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-full" style="background:#f59e0b"></span> Pending
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-full" style="background:#22c55e"></span> Approved
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-full" style="background:#16a34a"></span> Completed
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-full" style="background:#ef4444"></span> Rejected
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-full" style="background:#94a3b8"></span> Cancelled
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-full" style="background:#3b82f6"></span> For Revision
        </span>
    </div>

    {{-- Calendar --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow border border-gray-100 dark:border-gray-700">
        <div id="calendar"></div>
    </div>

    {{-- Detail Panel --}}
    <div id="to-detail-panel"
        class="hidden mt-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">

        <div id="to-detail-header" class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <span id="to-detail-badge"
                    class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white"
                    style="background:#3b82f6">
                </span>
                <span id="to-detail-tocode" class="text-lg font-semibold text-gray-800 dark:text-gray-100"></span>
            </div>
            <div class="flex items-center gap-3">
                <a id="to-detail-viewlink"
                    href="#"
                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    View full travel order →
                </a>
                <button onclick="closeDetail()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 dark:divide-gray-700">

            <div class="px-6 py-5 space-y-4">
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Requestor</div>
                    <div id="to-detail-requestor" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Travel Dates</div>
                    <div id="to-detail-dates" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Status</div>
                    <div id="to-detail-status" class="text-sm font-medium"></div>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Origin</div>
                    <div id="to-detail-origin" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Destination</div>
                    <div id="to-detail-destination" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Vehicle</div>
                    <div id="to-detail-vehicle" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Purpose</div>
                    <div id="to-detail-purpose" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Traveler/s</div>
                    <div id="to-detail-travelers" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Funding Source</div>
                    <div id="to-detail-funding" class="text-sm font-medium text-gray-800 dark:text-gray-100"></div>
                </div>
            </div>

        </div>
    </div>

</x-filament-panels::page>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.css" rel="stylesheet">
<style>
    .dark #calendar .fc-toolbar-title,
    .dark #calendar .fc-col-header-cell-cushion,
    .dark #calendar .fc-daygrid-day-number,
    .dark #calendar .fc-button {
        color: #e5e7eb;
    }
    .dark #calendar .fc-theme-standard td,
    .dark #calendar .fc-theme-standard th,
    .dark #calendar .fc-theme-standard .fc-scrollgrid {
        border-color: #374151;
    }
    .dark #calendar .fc-daygrid-day {
        background-color: #1f2937;
    }
    .dark #calendar .fc-day-today {
        background-color: #1e3a5f !important;
    }
    .dark #calendar .fc-button-primary {
        background-color: #374151;
        border-color: #4b5563;
    }
    .dark #calendar .fc-button-primary:hover {
        background-color: #4b5563;
    }
    .dark #calendar .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    #calendar .fc-event {
        border-radius: 6px;
        padding: 2px 6px;
        font-size: 12px;
        cursor: pointer;
        white-space: normal !important;
        word-break: break-word;
        line-height: 1.3;
    }
    #calendar .fc-event-title {
        white-space: normal !important;
        word-break: break-word;
        overflow: visible !important;
    }
    #calendar .fc-daygrid-event-dot {
        display: none;
    }
    #calendar .fc-daygrid-event {
        align-items: flex-start !important;
    }
    #to-detail-panel {
        transition: all 0.2s ease;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script>
const ALL_EVENTS = @json($events);

const STATUS_COLORS = {
    'APPROVED':     '#22c55e',
    'COMPLETED':    '#16a34a',
    'PENDING':      '#f59e0b',
    'SUBMITTED':    '#f59e0b',
    'REJECTED':     '#ef4444',
    'CANCELLED':    '#94a3b8',
    'FOR_REVISION': '#3b82f6',
    'DRAFT':        '#94a3b8',
};

function closeDetail() {
    document.getElementById('to-detail-panel').classList.add('hidden');
}

function showDetail(eventId) {
    const ev = ALL_EVENTS.find(e => String(e.id) === String(eventId));
    if (!ev) return;

    const p     = ev.extendedProps ?? {};
    const panel = document.getElementById('to-detail-panel');
    const color = STATUS_COLORS[p.status] ?? '#3b82f6';

    document.getElementById('to-detail-badge').textContent      = p.status ?? '—';
    document.getElementById('to-detail-badge').style.background = color;

    // Show TO code if assigned, otherwise show the actual status word
    // p.to_code is now null (not 'Pending') when no code is assigned
    document.getElementById('to-detail-tocode').textContent = p.to_code ?? p.status ?? '—';

    document.getElementById('to-detail-viewlink').href           = ev.url ?? '#';
    document.getElementById('to-detail-requestor').textContent   = p.requestor ?? '—';
    document.getElementById('to-detail-dates').textContent       = (p.start_date ?? '—') + ' – ' + (p.end_date ?? '—');
    document.getElementById('to-detail-status').textContent      = p.status ?? '—';
    document.getElementById('to-detail-status').style.color      = color;
    document.getElementById('to-detail-origin').textContent      = p.origin ?? '—';
    document.getElementById('to-detail-destination').textContent = p.destination ?? '—';
    document.getElementById('to-detail-vehicle').textContent     = p.vehicle ?? 'Not specified';
    document.getElementById('to-detail-purpose').textContent     = p.purpose ?? '—';
    document.getElementById('to-detail-travelers').textContent   = p.travelers ?? '—';
    document.getElementById('to-detail-funding').textContent     = p.funding ?? '—';

    panel.classList.remove('hidden');
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

document.addEventListener('DOMContentLoaded', function () {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listMonth'
        },
        events: ALL_EVENTS,
        height: 'auto',
        nowIndicator: true,
        dayMaxEvents: 3,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showDetail(info.event.id);
        },
    });
    calendar.render();
});
</script>
@endpush