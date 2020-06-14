<?php

namespace BlockParty\Test;

use BlockParty\DynamicBlock;
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
        $file_location = $this->getTempLocation() . 'testSpreadsheetWithEmptyCompactBlockWorksheet.xlsx';

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

    public function testSpreadsheetWithSingleCompactBlockWorksheet()
    {
        $file_location = $this->getTempLocation() . 'testSpreadsheetWithSingleCompactBlockWorksheet.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->disconnectWorksheets();

        $dynamic_block = new DynamicBlock();
        $dynamic_block->addCell('B2', 'test');
        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block);
        $spreadsheet->addSheet($compact_block_worksheet);

        $xlsx_writer = new XlsxWriter($spreadsheet);
        $xlsx_writer->save($file_location);

        $xlsx_reader = new XlsxReader();
        $rendered_spreadsheet = $xlsx_reader->load($file_location);

        $rendered_worksheet = $rendered_spreadsheet->getSheet(0);
        $rendered_cells = $rendered_worksheet->getCellCollection();

        $error_message = "Failed XLSX file viewable at: '${file_location}'";
        $this->assertEquals(1, count($rendered_cells->getCoordinates()));
        $this->assertTrue($rendered_cells->has('B2'), $error_message);
        $this->assertEquals('test', $rendered_cells->get('B2')->getValue());
        unlink($file_location);
    }
}
