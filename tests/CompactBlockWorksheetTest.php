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
     * Test that an empty CompactBlockWorksheet produces correct output
     *
     * @return void
     */
    public function testSpreadsheetWithEmptyCompactBlockWorksheet()
    {
        $compact_block_worksheet = new CompactBlockWorksheet();
        $expected_coordinate_values = array();
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with a single block produces correct output
     *
     * @return void
     */
    public function testSpreadsheetWithSingleCompactBlockWorksheet()
    {
        $dynamic_block = new DynamicBlock();
        $dynamic_block->addCell('B2', 'test');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block);

        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, ['B2' => 'test'], __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that calling appendBlockToLastRow on empty worksheet is
     * the same as calling addBlockAsRow on an empty worksheet
     *
     * @return void
     */
    public function testAppendBlockAsColumnWorksOnEmptyCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->AddCell('A1', 'block');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->appendBlockToLastRow($dynamic_block_1);

        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, ['A1' => 'block'], __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with multiple blocks added to same row renders correctly
     *
     * @return void
     */
    public function testSpreadsheetWithMultipleBlocksInSingleRowOnCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B2', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('A1', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->appendBlockToLastRow($dynamic_block_1)
                                ->appendBlockToLastRow($dynamic_block_2)
                                ->appendBlockToLastRow($dynamic_block_3);

        $expected_coordinate_values = array('B2' => 'block 1',
                                            'E4' => 'block 2',
                                            'F1' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with multiple blocks added as separate rows renders correctly
     *
     * @return void
     */
    public function testSpreadsheetWithMultipleBlocksInSingleColumnOnCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B5', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('A3', 'block 3');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->addBlockAsRow($dynamic_block_3);

        $expected_coordinate_values = array('B5' => 'block 1',
                                            'C9' => 'block 2',
                                            'A12' => 'block 3');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Test that a CompactBlockWorksheet with multiple blocks added as rows and columns
     * renders correctly, also test the CompactBlockWorksheet::appendBlockToRow method
     *
     * @return void
     */
    public function testSpreadsheetWithMultipleRowsAndColumnsOnCompactBlockWorksheet()
    {
        $dynamic_block_1 = new DynamicBlock();
        $dynamic_block_1->addCell('B5', 'block 1');

        $dynamic_block_2 = new DynamicBlock();
        $dynamic_block_2->addCell('C4', 'block 2');

        $dynamic_block_3 = new DynamicBlock();
        $dynamic_block_3->addCell('A3', 'block 3');

        $dynamic_block_4 = new DynamicBlock();
        $dynamic_block_4->addCell('D2', 'block 4');

        $compact_block_worksheet = new CompactBlockWorksheet();
        $compact_block_worksheet->addBlockAsRow($dynamic_block_1)
                                ->addBlockAsRow($dynamic_block_2)
                                ->appendBlockToRow($dynamic_block_3, 1)
                                ->appendBlockToRow($dynamic_block_4, 2);

        $expected_coordinate_values = array('B5' => 'block 1',
                                            'C9' => 'block 2',
                                            'C3' => 'block 3',
                                            'G7' => 'block 4');
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, __FUNCTION__ . '.xlsx');
    }

    /**
     * Assert that a BlockWorksheet produces specific values when saved on a spreadsheet
     *
     * @param  BlockWorksheet $worksheet
     * @param  Iterable       $expected_coordinate_values
     * @param  string         $temp_file_name
     *
     * @return void
     */
    private function assertBlockWorksheetProducesExepectedResults($worksheet, $expected_coordinate_values, $temp_file_name)
    {
        $file_location = $this->getTempLocation() . $temp_file_name;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->disconnectWorksheets();
        $spreadsheet->addSheet($worksheet);

        $xlsx_writer = new XlsxWriter($spreadsheet);
        $xlsx_writer->save($file_location);

        $xlsx_reader = new XlsxReader();
        $rendered_spreadsheet = $xlsx_reader->load($file_location);

        $rendered_worksheet = $rendered_spreadsheet->getSheet(0);
        $rendered_cells = $rendered_worksheet->getCellCollection();

        $error_message = "Failed XLSX file viewable at: '${file_location}'";
        $this->assertEquals(count($expected_coordinate_values), count($rendered_cells->getCoordinates()), $error_message);
        foreach ($expected_coordinate_values as $coordinate => $value) {
            $this->assertTrue($rendered_cells->has($coordinate), $error_message);
            $this->assertEquals($value, $rendered_cells->get($coordinate)->getValue());
        }
        unlink($file_location);
    }
}
