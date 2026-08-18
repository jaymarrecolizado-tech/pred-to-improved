<?php

namespace App\Services;

use App\Models\TravelOrder;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TevPackService
{
    public function templatePath(): string
    {
        return resource_path('templates/TEV_TEMPLATE.xlsx');
    }

    public function download(TravelOrder $travelOrder): StreamedResponse
    {
        $spreadsheet = $this->make($travelOrder);
        $filename = $this->filename($travelOrder);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function filename(TravelOrder $travelOrder): string
    {
        $lastName = $this->primaryLastName($travelOrder);
        $year = optional($travelOrder->start_date)->format('Y') ?: date('Y');
        $dest = $this->destinationSlug($travelOrder);

        if ($travelOrder->status !== 'COMPLETED' || empty($travelOrder->to_code)) {
            $dest = 'DRAFT';
        }

        return 'TEV.' . $lastName . '_' . $dest . $year . '.xlsx';
    }

    public function make(TravelOrder $travelOrder): Spreadsheet
    {
        $path = $this->templatePath();
        if (!is_file($path)) {
            throw new \RuntimeException('TEV template is missing. Expected: ' . $path);
        }

        $spreadsheet = IOFactory::load($path);
        $travelOrder->loadMissing('user');

        $names = $this->travelerNames($travelOrder);
        $positions = $this->travelerPositions($travelOrder);
        $dateRange = $this->dateRange($travelOrder);
        $purpose = trim((string) ($travelOrder->purpose ?? ''));
        $purposeParts = $this->splitPurpose($purpose);
        $station = $this->officialStation($travelOrder);
        $toNo = ($travelOrder->status === 'COMPLETED' && $travelOrder->to_code)
            ? $travelOrder->to_code
            : 'Pending';
        $toDate = $this->toDateLabel($travelOrder);
        $destinations = $this->destinationList($travelOrder);
        $rows = $this->itineraryRows($travelOrder);

        $this->fillItinerary($spreadsheet->getSheetByName('Itinerary') ?? $spreadsheet->getSheet(0), [
            'names' => $names,
            'positions' => $positions,
            'dateRange' => $dateRange,
            'purpose1' => $purposeParts[0],
            'purpose2' => $purposeParts[1],
            'station' => $station,
            'toNo' => $toNo,
            'rows' => $rows,
        ]);

        $this->fillAppendix47($spreadsheet->getSheetByName('Appendix 47') ?? $spreadsheet->getSheet(1), [
            'toNo' => $toNo,
            'toDate' => $toDate,
            'names' => $names,
            'destinations' => $destinations,
        ]);

        $this->fillAfterTravelReport($spreadsheet->getSheetByName('After Travel Report') ?? $spreadsheet->getSheet(2), [
            'toNo' => $toNo,
            'toDate' => $toDate,
            'dateRange' => $dateRange,
            'names' => $names,
            'positions' => $positions,
            'destinations' => $destinations,
            'purpose' => $purpose,
        ]);

        return $spreadsheet;
    }

    private function fillItinerary(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('Z12', $data['toNo']);
        $sheet->setCellValue('E14', $data['names']);
        $sheet->setCellValue('V14', $data['dateRange']);
        $sheet->setCellValue('E15', $data['positions']);
        $sheet->setCellValue('V15', $data['purpose1']);
        $sheet->setCellValue('R16', $data['purpose2']);
        $sheet->setCellValue('E16', $data['station']);
        $sheet->setCellValue('S44', $data['names']);

        $startRow = 20;
        $footerRow = 33;
        $capacity = $footerRow - $startRow;
        $needed = max(count($data['rows']), 1);

        if ($needed > $capacity) {
            $extra = $needed - $capacity;
            $sheet->insertNewRowBefore($footerRow, $extra);
            $footerRow += $extra;
        }

        foreach ($data['rows'] as $i => $row) {
            $excelRow = $startRow + $i;
            $sheet->setCellValue('A' . $excelRow, $row['date']);
            $sheet->setCellValue('D' . $excelRow, $row['place']);
            $sheet->getStyle('A' . $excelRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $excelRow)->getAlignment()->setWrapText(true);
        }

        $used = $startRow + count($data['rows']);
        for ($excelRow = $used; $excelRow < $footerRow; $excelRow++) {
            foreach (['A', 'D', 'J', 'M', 'P', 'T', 'W', 'X', 'Y', 'Z'] as $col) {
                $sheet->setCellValue($col . $excelRow, null);
            }
        }

        foreach (['T', 'W', 'X', 'Y', 'Z'] as $col) {
            $sheet->setCellValue($col . $footerRow, null);
        }
    }

    private function fillAppendix47(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('I19', $data['toNo']);
        $sheet->setCellValue('U19', $data['toDate']);
        $sheet->setCellValue('T37', $data['names']);
        $sheet->setCellValue('H27', null);
        $evidence = 'TRAVEL ORDER, APPENDIX 45 & 47, CERTIFICATE OF APPEARANCE, ACCOMPLISHMENT REPORT.';
        $sheet->setCellValue('B31', $evidence);
        $sheet->setCellValue(
            'B32',
            'Complete this section after travel. Destinations: ' . ($data['destinations'] ?: '—')
        );
    }

    private function fillAfterTravelReport(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('E14', 'After Travel Report');
        $sheet->setCellValue('E16', $data['dateRange']);
        $sheet->setCellValue(
            'B20',
            'Pursuant to Local Travel Order No. ' . $data['toNo'] . ' dated ' . $data['toDate'] . '; the undersigned'
        );
        $sheet->setCellValue('B21', 'proceeded to the following destinations, and the following were undertaken.');
        $sheet->setCellValue(
            'B22',
            '- ' . ($data['destinations'] ?: '—') . ($data['purpose'] ? ' — ' . $data['purpose'] : '')
        );
        $sheet->setCellValue('B32', 'Above stated activities were successfully undertaken.');
        $sheet->setCellValue('B41', $data['names']);
        $sheet->setCellValue('B42', $data['positions']);
    }

    private function itineraryRows(TravelOrder $travelOrder): array
    {
        $segments = is_array($travelOrder->travel_location) ? $travelOrder->travel_location : [];
        $rows = [];

        foreach ($segments as $segment) {
            if (!is_array($segment)) {
                continue;
            }

            $origin = trim((string) ($segment['origin'] ?? ''));
            $destination = trim((string) ($segment['destination'] ?? ''));
            $start = $this->parseDate($segment['start_date'] ?? null);
            $end = $this->parseDate($segment['end_date'] ?? null);

            $rows[] = [
                'date' => $start ? $start->format('F j, Y') : '',
                'place' => trim('Travel from ' . ($origin ?: 'origin') . ' to ' . ($destination ?: 'destination')),
            ];

            if ($start && $end && $start->toDateString() !== $end->toDateString()) {
                $rows[] = [
                    'date' => $start->format('M j') . ' - ' . $end->format('j, Y'),
                    'place' => 'Still at ' . ($destination ?: 'destination'),
                ];
            }
        }

        if ($rows === []) {
            $rows[] = [
                'date' => optional($travelOrder->start_date)->format('F j, Y') ?? '',
                'place' => '',
            ];
        }

        return $rows;
    }

    private function travelerNames(TravelOrder $travelOrder): string
    {
        $names = collect($travelOrder->travelers ?? [])
            ->map(fn ($traveler) => trim((string) (is_array($traveler) ? ($traveler['name'] ?? '') : $traveler)))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty() && $travelOrder->user?->name) {
            $names = collect([$travelOrder->user->name]);
        }

        return $names->implode(' / ');
    }

    private function travelerPositions(TravelOrder $travelOrder): string
    {
        return collect($travelOrder->travelers ?? [])
            ->map(fn ($traveler) => trim((string) (is_array($traveler) ? ($traveler['position'] ?? '') : '')))
            ->filter()
            ->unique()
            ->implode(' / ') ?: '';
    }

    private function dateRange(TravelOrder $travelOrder): string
    {
        $start = optional($travelOrder->start_date)->format('F j, Y');
        $end = optional($travelOrder->end_date)->format('F j, Y');

        if ($start && $end) {
            return $start . ' - ' . $end;
        }

        return $start ?: ($end ?: '');
    }

    private function officialStation(TravelOrder $travelOrder): string
    {
        $segments = is_array($travelOrder->travel_location) ? $travelOrder->travel_location : [];
        $origin = trim((string) (is_array($segments[0] ?? null) ? ($segments[0]['origin'] ?? '') : ''));

        return $origin !== '' ? $origin : 'DICT Regional Office';
    }

    private function destinationList(TravelOrder $travelOrder): string
    {
        return collect($travelOrder->travel_location ?? [])
            ->map(fn ($segment) => is_array($segment) ? trim((string) ($segment['destination'] ?? '')) : '')
            ->filter()
            ->unique()
            ->implode(', ');
    }

    private function destinationSlug(TravelOrder $travelOrder): string
    {
        $first = collect($travelOrder->travel_location ?? [])
            ->map(fn ($segment) => is_array($segment) ? trim((string) ($segment['destination'] ?? '')) : '')
            ->first(fn ($value) => $value !== '');

        $slug = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $first) ?? '');

        return $slug !== '' ? substr($slug, 0, 18) : 'TRAVEL';
    }

    private function primaryLastName(TravelOrder $travelOrder): string
    {
        $name = $this->travelerNames($travelOrder);
        $firstPerson = trim(explode('/', $name)[0] ?? '');
        $parts = preg_split('/\s+/', $firstPerson) ?: [];
        $last = strtoupper((string) (end($parts) ?: ($travelOrder->user->name ?? 'TRAVELER')));
        $last = preg_replace('/[^A-Z]/', '', $last) ?: 'TRAVELER';

        return $last;
    }

    private function toDateLabel(TravelOrder $travelOrder): string
    {
        $date = $travelOrder->completed_at
            ?? $travelOrder->submitted_at
            ?? $travelOrder->created_at;

        return $date ? Carbon::parse($date)->format('d F Y') : now()->format('d F Y');
    }

    private function splitPurpose(string $purpose): array
    {
        if (mb_strlen($purpose) <= 70) {
            return [$purpose, ''];
        }

        return [mb_substr($purpose, 0, 70), mb_substr($purpose, 70)];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
