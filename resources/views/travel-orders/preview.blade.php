{{-- Draft / pre-submit document preview (screen). Mirrors the completed TO PDF layout. --}}
@php
    $funds = is_array($travelOrder->other_funds) ? $travelOrder->other_funds : [];
    $toCode = $travelOrder->to_code ?: 'Pending';
    $isDraftPreview = in_array($travelOrder->status, ['DRAFT', 'FOR_REVISION'], true);
@endphp
<div class="to-preview-shell">
    @if ($isDraftPreview)
        <div class="to-preview-banner">
            DRAFT PREVIEW — Not yet submitted. Review carefully, then submit for approval.
        </div>
    @endif

    <div class="to-preview-page">
        <div class="to-preview-header">
            <img src="{{ asset('images/DICT_HEADER.png') }}" alt="DICT Header" class="to-preview-header-img">
        </div>

        <table class="to-title-table">
            <tr>
                <td class="to-title-line">
                    <strong>LOCAL TRAVEL ORDER No:
                        @if ($isDraftPreview)
                            <span class="to-draft-code">{{ $toCode }}</span>
                        @else
                            {{ $toCode }}
                        @endif
                    </strong><br>
                    <strong>Series of {{ optional($travelOrder->created_at)->format('Y') ?? date('Y') }}</strong>
                </td>
                <td class="to-date-line">
                    <strong>Date:</strong>
                    <span class="to-underline">{{ optional($travelOrder->created_at)->format('F d, Y') ?? now()->format('F d, Y') }}</span>
                </td>
            </tr>
        </table>

        <div class="to-authority">Authority to Travel is hereby granted to:</div>
        <div class="to-requester">Travel Order Requester: {{ $travelOrder->user->name ?? 'N/A' }}</div>

        <table class="to-employee-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Name</th>
                    <th style="width: 30%;">Position</th>
                    <th style="width: 35%;">Division/Agency</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($travelOrder->travelers ?? [] as $traveler)
                    <tr>
                        <td>{{ is_array($traveler) ? ($traveler['name'] ?? '') : $traveler }}</td>
                        <td>{{ is_array($traveler) ? ($traveler['position'] ?? '') : '' }}</td>
                        <td>{{ is_array($traveler) ? ($traveler['division'] ?? '') : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center;">No travelers listed</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="to-purpose-title">Destination/s and Inclusive Dates of Travel:</div>
        <table class="to-employee-table" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th style="width: 28%;">From</th>
                    <th style="width: 28%;">To</th>
                    <th style="width: 22%;">Start Date</th>
                    <th style="width: 22%;">End Date</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($travelOrder->travel_location) && is_array($travelOrder->travel_location))
                    @foreach ($travelOrder->travel_location as $segment)
                        <tr>
                            <td>{{ $segment['origin'] ?? 'N/A' }}</td>
                            <td>{{ $segment['destination'] ?? 'N/A' }}</td>
                            <td>
                                @if (!empty($segment['start_date']))
                                    {{ \Carbon\Carbon::parse($segment['start_date'])->format('M d, Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if (!empty($segment['end_date']))
                                    {{ \Carbon\Carbon::parse($segment['end_date'])->format('M d, Y') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center;">No itinerary specified</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="to-overall-period">
            <strong>Overall Travel Period:</strong>
            {{ optional($travelOrder->start_date)->format('F d, Y') }}
            &mdash;
            {{ optional($travelOrder->end_date)->format('F d, Y') }}
        </div>

        <div class="to-two-col">
            <div class="to-col to-col-40">
                <div class="to-field-label">Travel Expenses to be Incurred:</div>
                <div style="margin-top:5px;">
                    <span class="to-checkbox">{{ in_array('actual', $funds) ? '✓' : '' }}</span> Actual
                    <span class="to-checkbox">{{ in_array('incidental', $funds) ? '✓' : '' }}</span> Incidental
                    <span class="to-checkbox">{{ in_array('per_diem', $funds) ? '✓' : '' }}</span> Per Diem
                    <span class="to-checkbox">{{ in_array('official_time', $funds) ? '✓' : '' }}</span> Official Time
                    <span class="to-checkbox">{{ in_array('no_claim', $funds) ? '✓' : '' }}</span> No Claim
                    <span class="to-checkbox">{{ !empty($travelOrder->vehicle) ? '✓' : '' }}</span> Vehicle
                </div>
                <div class="to-field-label">Vehicle:</div>
                <span class="to-field-val">{{ $travelOrder->formattedVehicles() ?: 'N/A' }}</span>
            </div>
            <div class="to-col to-col-60">
                <div class="to-field-label">Funding Source:</div>
                <span class="to-field-val">
                    @if (is_array($travelOrder->travel_sources))
                        {{ implode(', ', $travelOrder->travel_sources) }}
                    @else
                        {{ $travelOrder->travel_sources ?: 'N/A' }}
                    @endif
                </span>
            </div>
        </div>

        <div class="to-purpose-section">
            <div class="to-purpose-title">Purpose of Travel:</div>
            <div class="to-purpose-content">{{ $travelOrder->purpose ?? '' }}</div>
        </div>

        <div class="to-purpose-section">
            <div class="to-purpose-title">Remarks:</div>
            <div class="to-purpose-content">{{ $travelOrder->remarks ?? '' }}</div>
        </div>

        <div class="to-notice">
            A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days from
            completion of travel. Liquidation of each advance should be made after the issuance of travel authority
            by the Regional Director.
        </div>

        <div class="to-disclaimer">
            @if ($isDraftPreview)
                This is a draft preview for checking only. Official Travel Order number and signatures appear after full approval.
            @else
                This document is an official system-generated Travel Order and carries full authority equivalent to a
                physical Travel Order.
            @endif
        </div>

        @php
            $attachments = is_array($travelOrder->attachment)
                ? array_filter($travelOrder->attachment)
                : ($travelOrder->attachment ? [$travelOrder->attachment] : []);
        @endphp
        @if (!empty($attachments))
            <div class="to-attachments">
                Attachment/s:
                @foreach ($attachments as $file)
                    <a href="{{ asset('storage/' . $file) }}" target="_blank" rel="noopener">
                        {{ basename($file) }}
                    </a>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </div>
        @endif

        <table class="to-sig-table">
            <tr>
                <td class="to-sig-header">RECOMMENDING APPROVAL:</td>
                <td class="to-sig-header">APPROVED:</td>
                <td></td>
            </tr>
            <tr>
                <td style="height: 48px;"></td>
                <td style="height: 48px;"></td>
                <td></td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <span class="to-sig-name">&nbsp;</span>
                    <span class="to-sig-position">{{ $isDraftPreview ? '(After approval)' : '' }}</span>
                </td>
                <td style="vertical-align: top;">
                    <span class="to-sig-name">&nbsp;</span>
                    <span class="to-sig-position">{{ $isDraftPreview ? '(After approval)' : '' }}</span>
                </td>
                <td></td>
            </tr>
        </table>

        <div class="to-preview-footer">
            <img src="{{ asset('images/DICT_FOOTER.jpg') }}" alt="DICT Footer" class="to-preview-footer-img">
        </div>
    </div>
</div>

<style>
    .to-preview-shell { max-width: 820px; margin: 0 auto; }
    .to-preview-banner {
        background: #f59e0b;
        color: #1f2937;
        font-weight: 700;
        font-size: 0.875rem;
        text-align: center;
        padding: 0.65rem 1rem;
        border-radius: 0.5rem 0.5rem 0 0;
        letter-spacing: 0.02em;
    }
    .to-preview-page {
        background: #fff;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        line-height: 1.35;
        padding: 1.5rem 1.75rem 1.25rem;
        border: 1px solid #d1d5db;
        border-radius: {{ $isDraftPreview ? '0 0 0.5rem 0.5rem' : '0.5rem' }};
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .to-preview-header { text-align: center; margin-bottom: 0.75rem; }
    .to-preview-header-img { width: 70%; max-width: 480px; height: auto; object-fit: contain; }
    .to-preview-footer { text-align: center; margin-top: 1.25rem; }
    .to-preview-footer-img { width: 100%; max-height: 70px; object-fit: contain; }
    .to-title-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .to-title-line { font-size: 12px; text-align: left; }
    .to-date-line { font-size: 12px; text-align: right; }
    .to-underline {
        border-bottom: 1px solid #000;
        display: inline-block;
        min-width: 8rem;
        font-weight: bold;
        font-style: italic;
        text-align: center;
        padding: 0 0.25rem;
    }
    .to-draft-code { color: #b45309; font-style: italic; }
    .to-authority { margin: 12px 0 8px; font-weight: bold; }
    .to-requester { margin: 0 0 12px; }
    .to-employee-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; table-layout: fixed; }
    .to-employee-table th, .to-employee-table td {
        padding: 5px 6px; font-size: 11px; vertical-align: top; word-wrap: break-word;
    }
    .to-employee-table th { font-weight: bold; text-align: left; border-bottom: 1.5px solid #000; }
    .to-employee-table td { border-bottom: 1px solid #ccc; }
    .to-overall-period { margin: 6px 0 12px; font-size: 11px; color: #333; }
    .to-two-col { display: flex; gap: 1rem; margin-bottom: 10px; }
    .to-col-40 { width: 42%; }
    .to-col-60 { width: 58%; }
    .to-field-label { font-weight: bold; margin-top: 6px; margin-bottom: 2px; display: block; font-size: 11px; }
    .to-field-val {
        border-bottom: 1px solid #000; padding: 2px 3px; display: block;
        font-weight: bold; font-style: italic; font-size: 11px; min-height: 1.2em;
    }
    .to-checkbox {
        display: inline-block; width: 14px; height: 14px; border: 1px solid #000;
        margin-right: 3px; text-align: center; vertical-align: middle; line-height: 12px; font-size: 11px;
    }
    .to-purpose-section { margin-top: 10px; }
    .to-purpose-title { font-weight: bold; margin-bottom: 3px; font-size: 12px; }
    .to-purpose-content {
        border-bottom: 1px solid #000; padding: 5px 0; min-height: 18px;
        text-align: center; font-size: 11px;
    }
    .to-notice { font-size: 11px; margin-top: 12px; text-align: justify; line-height: 1.4; }
    .to-disclaimer {
        font-size: 10px; text-align: center; color: #555; font-style: italic;
        margin: 12px 0; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 6px 0;
    }
    .to-attachments { font-size: 10px; margin: 10px 0 16px; }
    .to-attachments a { color: #0066cc; text-decoration: underline; }
    .to-sig-table { width: 100%; margin-top: 20px; border-collapse: collapse; table-layout: fixed; }
    .to-sig-table td { vertical-align: bottom; padding: 2px 5px; width: 33.33%; text-align: center; }
    .to-sig-header { font-weight: bold; font-size: 12px; text-align: left; padding-bottom: 10px; }
    .to-sig-name {
        font-weight: bold; font-size: 11px; text-transform: uppercase; text-align: center;
        border-bottom: 1px solid #000; margin: 0 auto; width: 90%; display: block; min-height: 1.2em;
    }
    .to-sig-position { font-size: 10px; color: #444; text-align: center; padding-top: 2px; display: block; }
    @media (max-width: 640px) {
        .to-two-col { flex-direction: column; }
        .to-col-40, .to-col-60 { width: 100%; }
        .to-preview-page { padding: 1rem; }
    }
</style>
