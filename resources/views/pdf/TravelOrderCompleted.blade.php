<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Order</title>
    <style>
        @page { size: A4; margin: 30mm 15mm 25mm 15mm; }
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.3; margin: 0; padding: 0; color: #000; }
        .header { position: fixed; top: -20mm; left: 0; right: 0; text-align: center; height: 80px; }
        .header-img { width: 60%; height: auto; max-height: 80px; object-fit: contain; }
        .footer { position: fixed; bottom: -15mm; left: 0; right: 0; text-align: center; height: 80px; }
        .footer-img { width: 100%; height: 70%; }
        .page-container { width: 100%; box-sizing: border-box; }
        .title-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .title-line { font-size: 10px; line-height: 1.4; text-align: left; }
        .date-line { font-size: 10px; text-align: right; line-height: 1.4; }
        .underline { border-bottom: 1px solid black; display: inline-block; width: auto; vertical-align: bottom; font-weight: bold; font-style: italic; text-align: center; }
        .authority-text { margin: 12px 0 8px; font-weight: bold; }
        .travel-request { margin: 8px 0 12px; }
        .employee-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; table-layout: fixed; }
        .employee-table th, .employee-table td { padding: 5px 6px; font-size: 9.5px; vertical-align: top; word-wrap: break-word; }
        .employee-table th { font-weight: bold; text-align: left; border-bottom: 1.5px solid #000; }
        .employee-table td { border-bottom: 1px solid #ccc; }
        .two-col { display: table; width: 100%; margin-bottom: 8px; table-layout: fixed; }
        .two-col-inner { display: table-row; }
        .col { display: table-cell; vertical-align: top; padding: 3px 0; }
        .col-40 { width: 42%; padding-right: 10px; }
        .col-60 { width: 58%; padding-left: 5px; }
        .field-label { font-weight: bold; margin-top: 5px; margin-bottom: 2px; display: block; font-size: 9.5px; }
        .field-val { border-bottom: 1px solid #000; padding: 2px 3px; display: block; font-weight: bold; font-style: italic; font-size: 9.5px; }
        .checkbox-box { display: inline-block; width: 14px; height: 14px; border: 1px solid #000; margin-right: 3px; text-align: center; vertical-align: middle; font-family: "DejaVu Sans", sans-serif; }
        .purpose-section { margin-top: 8px; }
        .purpose-title { font-weight: bold; margin-bottom: 3px; font-size: 10px; }
        .purpose-content { border-bottom: 1px solid #000; padding: 5px 0; min-height: 16px; text-align: center; font-size: 9.5px; }
        .notice { font-size: 9px; margin-top: 10px; text-align: justify; line-height: 1.4; }
        .disclaimer { font-size: 8.5px; text-align: center; color: #555; font-style: italic; margin: 10px 0; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 4px 0; }
        .attachment-section { font-size: 8px; margin: 10px 0 20px 0; }
        .attachment-section a { color: #0066cc; text-decoration: underline; }
        .sig-table { width: 100%; margin-top: 20px; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; }
        .sig-table td { vertical-align: bottom; padding: 2px 5px; width: 33.33%; text-align: center; }
        .sig-header { font-weight: bold; font-size: 10px; text-align: left; padding-bottom: 10px; padding-left: 5px; }
        .sig-img { max-width: 130px; max-height: 60px; display: block; margin: 0 auto -12px auto; object-fit: contain; }
        .sig-name { font-weight: bold; font-size: 9.5px; text-transform: uppercase; text-align: center; border-bottom: 1px solid #000; margin: 0 auto; width: 90%; display: block; }
        .sig-position { font-size: 8.5px; color: #444; text-align: center; padding-top: 2px; display: block; }
        .initials-block { margin-top: 10px; display: table; width: auto; margin-left: auto; margin-right: 0; }
        .initials-item { display: table-cell; text-align: center; vertical-align: bottom; padding: 0 3px; }
        .initials-sig-img { max-width: 55px; max-height: 26px; display: block; margin: 0 auto; }
        .overall-period { margin-top: 5px; font-size: 9px; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/DICT_HEADER.png') }}" class="header-img">
    </div>
    <div class="footer">
        <img src="{{ public_path('images/DICT_FOOTER.jpg') }}" class="footer-img">
    </div>
    <div class="page-container">

        <table class="title-table">
            <tr>
                <td class="title-line">
                    <strong>LOCAL TRAVEL ORDER No: {{ $travelOrder->to_code }}</strong><br>
                    <strong>Series of {{ date('Y', strtotime($travelOrder->created_at)) }}</strong>
                </td>
                <td class="date-line">
                    <strong>Date:</strong>
                    <span class="underline">{{ date('F d, Y', strtotime($travelOrder->created_at)) }}</span>
                </td>
            </tr>
        </table>

        <div class="authority-text">Authority to Travel is hereby granted to:</div>
        <div class="travel-request">Travel Order Requester: {{ $travelOrder->user->name }}</div>

        <table class="employee-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Name</th>
                    <th style="width: 30%;">Position</th>
                    <th style="width: 35%;">Division/Agency</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($travelOrder->travelers as $traveler)
                    <tr>
                        <td>{{ $traveler['name'] ?? '' }}</td>
                        <td>{{ $traveler['position'] ?? '' }}</td>
                        <td>{{ $traveler['division'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Destination/s and Inclusive Dates merged into one table with per-segment dates --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; table-layout: fixed;">
            <tr>
                <td style="width: 100%; vertical-align: top;">
                    <div class="purpose-title">Destination/s and Inclusive Dates of Travel:</div>
                    <table class="employee-table" style="margin-bottom: 0;">
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
                                                {{ date('M d, Y', strtotime($segment['start_date'])) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($segment['end_date']))
                                                {{ date('M d, Y', strtotime($segment['end_date'])) }}
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
                    <div class="overall-period">
                        <strong>Overall Travel Period:</strong>
                        {{ date('F d, Y', strtotime($travelOrder->start_date)) }}
                        &mdash;
                        {{ date('F d, Y', strtotime($travelOrder->end_date)) }}
                    </div>
                </td>
            </tr>
        </table>

        @php
            $funds = is_array($travelOrder->other_funds) ? $travelOrder->other_funds : [];
        @endphp

        <div class="two-col">
            <div class="two-col-inner">
                <div class="col col-40">
                    <div class="field-label">Travel Expenses to be Incurred:</div>
                    <div style="margin-top:5px;">
                        <span class="checkbox-box">{{ in_array('actual', $funds) ? '✓' : '' }}</span> Actual
                        <span class="checkbox-box">{{ in_array('incidental', $funds) ? '✓' : '' }}</span> Incidental
                        <span class="checkbox-box">{{ in_array('per_diem', $funds) ? '✓' : '' }}</span> Per Diem
                        <span class="checkbox-box">{{ in_array('official_time', $funds) ? '✓' : '' }}</span> Official Time
                        <span class="checkbox-box">{{ in_array('no_claim', $funds) ? '✓' : '' }}</span> No Claim
                        <span class="checkbox-box">{{ !empty($travelOrder->vehicle) ? '✓' : '' }}</span> Vehicle
                    </div>
                    <div class="field-label">Vehicle:</div>
                    <span class="field-val">{{ $travelOrder->vehicle ?? 'N/A' }}</span>
                </div>
                <div class="col col-60">
                    <div class="field-label">Funding Source:</div>
                    <span class="field-val">
                        @if (is_array($travelOrder->travel_sources))
                            {{ implode(', ', $travelOrder->travel_sources) }}
                        @else
                            {{ $travelOrder->travel_sources }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="purpose-section">
            <div class="purpose-title">Purpose of Travel:</div>
            <div class="purpose-content">{{ $travelOrder->purpose ?? '' }}</div>
        </div>

        <div class="purpose-section">
            <div class="purpose-title">Remarks:</div>
            <div class="purpose-content">{{ $travelOrder->remarks ?? '' }}</div>
        </div>

        <div class="notice">
            A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days from
            completion of travel. Liquidation of each advance should be made after the issuance of travel authority
            by the Regional Director.
        </div>

        <div class="disclaimer">
            This document is an official system-generated Travel Order and carries full authority equivalent to a
            physical Travel Order.
        </div>

        <div class="attachment-section">
            @php
                $attachments = is_array($travelOrder->attachment)
                    ? array_filter($travelOrder->attachment)
                    : ($travelOrder->attachment ? [$travelOrder->attachment] : []);
            @endphp
            @if (!empty($attachments))
                Attachment/s:
                @foreach ($attachments as $file)
                    <a href="{{ asset('storage/' . $file) }}" target="_blank">
                        {{ basename($file) }}
                    </a>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @endif
        </div>

        @php
            $approvedSteps = $travelOrder->approvals()
                ->where('status', 'APPROVED')
                ->with(['workflow', 'approver.employee'])
                ->get();

            $recommendStep = $approvedSteps->first(fn($a) =>
                $a->workflow && ($a->workflow->to_recommend || $a->workflow->to_admin_recommend)
            );

            if (!$recommendStep) {
                $recommendStep = $approvedSteps->first();
            }

            $approveStep = $approvedSteps->first(fn($a) =>
                $a->workflow && $a->workflow->to_oic_rd
            );

            $isOicRd = $approveStep !== null;

            if (!$approveStep) {
                $approveStep = $approvedSteps->first(fn($a) =>
                    $a->workflow && $a->workflow->to_approve
                );
            }

            $poInitials = $approvedSteps->filter(fn($a) =>
                $a->workflow &&
                ($a->workflow->to_provincial_officer || $a->workflow->to_provincial_officer_two)
            );

            $regionalInitials = $approvedSteps->filter(fn($a) =>
                $a->workflow &&
                ($a->workflow->to_admin_initial || $a->workflow->to_ard_initial)
            );

            $toBase64 = function (?string $path) {
                if (!$path) return null;
                $full = storage_path('app/public/' . $path);
                if (!file_exists($full)) return null;
                $ext = pathinfo($full, PATHINFO_EXTENSION);
                return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($full));
            };

            $recommendName = $recommendStep?->approver_name_snapshot
                ?? $recommendStep?->approver?->name
                ?? '';

            $recommendPosition = $recommendStep?->approver_position_snapshot
                ?? $recommendStep?->approver?->employee?->position
                ?? ($recommendStep?->workflow?->to_admin_recommend
                    ? 'Chief, Admin. and Finance Division'
                    : 'Chief, Technical Operations Division');

            $approveName = $approveStep?->approver_name_snapshot
                ?? $approveStep?->approver?->name
                ?? '';

            if ($isOicRd) {
                $approvePosition = 'OIC, Regional Director';
            } else {
                $approvePosition = $approveStep?->approver_position_snapshot
                    ?? $approveStep?->approver?->employee?->position
                    ?? 'Regional Director';
            }

            $recSig = $recommendStep
                ? $toBase64($recommendStep->approver_signature_snapshot
                    ?? $recommendStep->approver?->signature)
                : null;

            $appSig = $approveStep
                ? $toBase64($approveStep->approver_signature_snapshot
                    ?? $approveStep->approver?->signature)
                : null;
        @endphp

        <table class="sig-table">
            <tr>
                <td class="sig-header">RECOMMENDING APPROVAL:</td>
                <td class="sig-header">APPROVED:</td>
                <td></td>
            </tr>
            <tr>
                <td>
                    @if ($recSig)
                        <img src="{{ $recSig }}" class="sig-img">
                    @endif
                </td>
                <td>
                    @if ($appSig)
                        <img src="{{ $appSig }}" class="sig-img">
                    @endif
                </td>
                <td></td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <span class="sig-name">{{ $recommendName }}</span>
                    <span class="sig-position">{{ $recommendPosition }}</span>
                    @if ($poInitials->count() > 0)
                        <div class="initials-block">
                            @foreach ($poInitials as $init)
                                @php
                                    $initSig = $toBase64(
                                        $init->approver_signature_snapshot
                                        ?? $init->approver?->signature
                                    );
                                @endphp
                                <div class="initials-item">
                                    @if ($initSig)
                                        <img src="{{ $initSig }}" class="initials-sig-img">
                                    @else
                                        <div style="height: 26px; width: 55px;"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <span class="sig-name">{{ $approveName }}</span>
                    <span class="sig-position">{{ $approvePosition }}</span>
                    @if ($regionalInitials->count() > 0)
                        <div class="initials-block">
                            @foreach ($regionalInitials as $init)
                                @php
                                    $initSig = $toBase64(
                                        $init->approver_signature_snapshot
                                        ?? $init->approver?->signature
                                    );
                                @endphp
                                <div class="initials-item">
                                    @if ($initSig)
                                        <img src="{{ $initSig }}" class="initials-sig-img">
                                    @else
                                        <div style="height: 26px; width: 55px;"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td></td>
            </tr>
        </table>

    </div>
</body>
</html>