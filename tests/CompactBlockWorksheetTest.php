<?php

namespace BlockParty\Test;

use BlockParty\CompactBlockWorksheet;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

final class CompactBlockWorksheetTest extends TestCase
{
    /**
     * Get temporary file location for test
     *
     * @return string
     */
    private function getTempLocation()
    {
        $temp_dir = dirname(__FILE__) . '/tmp/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir);
        }
        return $temp_dir;
    }

    /**
     * Test that a spreadsheet with an empty CompactBlockWorksheet doesn't render any cells
     *
     * @return void
     */
    public function testSpreadsheetWithEmptyCompactBlockWorksheet()
    {
        $file_location = $this->getTempLocation() . 'testEmptyCompactBlockWorksheet.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->disconnectWorksheets();

        $compact_block_worksheet = new CompactBlockWorksheet();
        $spreadsheet->addSheet($compact_block_worksheet);

        $xlsx_writer = new XlsxWriter($spreadsheet);
        $xlsx_writer->save($file_location);

        $xlsx_reader = new XlsxReader();
        $rendered_spreadsheet = $xlsx_reader->load($file_location);

        $rendered_worksheet = $rendered_spreadsheet->getSheet(0);
        $rendered_cells = $rendered_worksheet->getCellCollection();

        $this->assertEmpty($rendered_cells->getCoordinates(), "Failed XLSX file viewable at: '${file_location}'");
        unlink($file_location);
    }
}
    /**
