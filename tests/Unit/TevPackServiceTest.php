<?php

namespace Tests\Unit;

use App\Models\TravelOrder;
use App\Services\TevPackService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class TevPackServiceTest extends TestCase
{
    public function test_approved_travel_order_uses_to_code_instead_of_pending(): void
    {
        $spreadsheet = $this->makeTev($this->order([
            'status' => 'COMPLETED',
            'to_code' => '2026-02-150',
        ]));

        $appendix = $spreadsheet->getSheetByName('Appendix 47');
        $report = $spreadsheet->getSheetByName('After Travel Report');

        $this->assertSame('2026-02-150', $appendix->getCell('I19')->getValue());
        $this->assertSame('X', $appendix->getCell('H22')->getValue());
        $this->assertNull($appendix->getCell('H27')->getValue());
        $this->assertStringContainsString(
            'Local Travel Order No. 2026-02-150',
            (string) $report->getCell('B20')->getValue()
        );
        $this->assertFalse(
            str_contains((string) $report->getCell('B20')->getValue(), 'Pending')
        );
    }

    public function test_draft_travel_order_keeps_pending_to_number(): void
    {
        $spreadsheet = $this->makeTev($this->order([
            'status' => 'DRAFT',
            'to_code' => null,
        ]));

        $this->assertSame(
            'Pending',
            $spreadsheet->getSheetByName('Appendix 47')->getCell('I19')->getValue()
        );
    }

    public function test_after_travel_report_reuses_appendix_47_letterhead(): void
    {
        $spreadsheet = $this->makeTev($this->order([
            'status' => 'COMPLETED',
            'to_code' => '2026-02-150',
        ]));

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tev-pack-test.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        try {
            $reloaded = IOFactory::load($path);
            $appendix = $reloaded->getSheetByName('Appendix 47');
            $report = $reloaded->getSheetByName('After Travel Report');

            $this->assertGreaterThan(0, $appendix->getDrawingCollection()->count());
            $this->assertGreaterThan(0, $report->getDrawingCollection()->count());

            $source = $appendix->getDrawingCollection()[0];
            $copied = $report->getDrawingCollection()[0];

            $this->assertSame($source->getCoordinates(), $copied->getCoordinates());
            $this->assertSame($source->getWidth(), $copied->getWidth());
            $this->assertSame($source->getHeight(), $copied->getHeight());
        } finally {
            @unlink($path);
        }
    }

    private function makeTev(TravelOrder $order)
    {
        return (new TevPackService())->make($order);
    }

    private function order(array $overrides): TravelOrder
    {
        return new TravelOrder(array_merge([
            'status' => 'COMPLETED',
            'to_code' => '2026-02-150',
            'purpose' => 'For the Record Test',
            'start_date' => '2026-08-18',
            'end_date' => '2026-08-19',
            'completed_at' => '2026-08-18',
            'travelers' => [
                ['name' => 'Juan Dela Cruz', 'position' => 'ITO'],
            ],
            'travel_location' => [[
                'origin' => 'Tuguegarao',
                'destination' => 'San Mateo',
                'start_date' => '2026-08-18',
                'end_date' => '2026-08-19',
            ]],
        ], $overrides));
    }
}
