<?php

namespace OfficeBlockParty\Test;

use OfficeBlockParty\DynamicBlock;
use OfficeBlockParty\CompactBlockWorksheet;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

final class CompactBlockWorksheetTest extends TestCase
{
    /**
     * Data provider for testing invalidArgumentException when adding a block to a CompactBlockWorksheet
     * See `testInvalidArgumentsOnBlockInsertion` for description of returned parameters
     *
     * @return array
     */
    public function invalidArgumentDataProvider()
    {
        return array(
            "Test that appendBlockToRow throws an exception when the supplied row is zero" =>
                [[], 'appendBlockToRow', [0]],

            "Test that appendBlockToRow throws an exception when the supplied row is a negative value" =>
                [[], 'appendBlockToRow', [-1]],

            "Test that appendBlockToRow throws an exception when the supplied row is greater than the number of existing rows" =>
                [['addBlockAsRow', 'addBlockAsRow'], 'appendBlockToRow', [3]],

            "Test that insertBlockAfterRow throws an exception when the supplied row is a negative value" =>
                [[], 'insertBlockAfterRow', [-1]],

            "Test that insertBlockAfterRow throws an exception when the supplied row is a greater than the number of existing rows" =>
                [['addBlockAsRow', 'addBlockAsRow'], 'insertBlockAfterRow', [3]],

            "Test that insertBlockBeforeRow throws an exception when the supplied row is zero" =>
                [[], 'insertBlockBeforeRow', [0]],

            "Test that insertBlockBeforeRow throws an exception when the supplied row is a negative value" =>
                [[], 'insertBlockBeforeRow', [-2]],

            "Test that insertBlockBeforeRow throws an exception when the supplied row is a greater than the number of existing rows" =>
                [['addBlockAsRow', 'addBlockAsRow'], 'insertBlockBeforeRow', [3]],

            "Test that insertBlockAfterColumn throws an exception when the supplied row is a negative value" =>
                [[], 'insertBlockAfterColumn', [-1, 1]],

            "Test that insertBlockAfterColumn throws an exception when the supplied column is a negative value" =>
                [[], 'insertBlockAfterColumn', [1, -1]],

            "Test that insertBlockAfterColumn throws an exception when the supplied row doesn't exist" =>
                [['addBlockAsRow', 'addBlockAsRow'], 'insertBlockAfterColumn', [3, 1]],

            "Test that insertBlockAfterColumn throws an exception when the supplied column doesn't exist" =>
                [['appendBlockToLastRow', 'appendBlockToLastRow'], 'insertBlockAfterColumn', [1, 3]],

            "Test that insertBlockBeforeColumn throws an exception when the supplied column is zero" =>
                [[], 'insertBlockBeforeColumn', [1, 0]],

            "Test that insertBlockBeforeColumn throws an exception when the supplied row is a negative value" =>
                [[], 'insertBlockBeforeColumn', [-1, 1]],

            "Test that insertBlockBeforeColumn throws an exception when the supplied column is a negative value" =>
                [[], 'insertBlockBeforeColumn', [1, -4]],

            "Test that insertBlockBeforeColumn throws an exception when the supplied row doesn't exist" =>
                [['addBlockAsRow', 'addBlockAsRow'], 'insertBlockBeforeColumn', [3, 1]],

            "Test that insertBlockBeforeColumn throws an exception when the supplied column doesn't exist" =>
                [['appendBlockToLastRow', 'appendBlockToLastRow'], 'insertBlockBeforeColumn', [1, 3]]
        );
    }

    /**
     * Test that adding blocks to a CompactBlockWorksheet throws
     * an InvalidArgumentException when supplied invalid parameters
     *
     * @dataProvider invalidArgumentDataProvider
     *
     * @param  array $pre_add_block_methods   An array of CompactBlockWorksheet methods used to add
     *                                            empty Dynamic blocks to it prior to expecting failure
     * @param  array $failure_method          Method to call with expected failure
     * @param  array $extra_args              Extra arguments to pass to $failure_method
     *
     * @return void
     */
    public function testInvalidArgumentsOnBlockInsertion($pre_add_block_methods, $failure_method, $extra_args)
    {
        $compact_block_worksheet = new CompactBlockWorksheet();
        foreach ($pre_add_block_methods as $block_method) {
            $compact_block_worksheet->$block_method(new DynamicBlock());
        }
        $this->expectException(\InvalidArgumentException::class);
        $compact_block_worksheet->$failure_method(new DynamicBlock(), ...$extra_args);
    }

    /**
     * Data Provider for testing inserting blocks into a CompactBlockWorksheet.
     * See `testWorksheetBlockInsertionMethods` for descritption of returned parameter
     *
     * @return array
     */
    public function blockWorksheetInsertionDataProvider()
    {
        return array(
            'Test that an empty CompactBlockWorksheet produces correct output' =>
                array(
                    'testSpreadsheetWithEmptyCompactBlockWorksheet',
                    array()
                ),
            'Test that a CompactBlockWorksheet with a single block produces correct output' =>
                array(
                    'testSpreadsheetWithSingleCompactBlockWorksheet',
                    array(
                        ['B2', 'B2', 'addBlockAsRow', []]
                    )
                ),
            'Test that calling appendBlockToLastRow on empty worksheet is the same as calling addBlockAsRow on an empty worksheet' =>
                array(
                    'testAppendBlockAsColumnWorksOnEmptyCompactBlockWorksheet',
                    array(
                        ['A1', 'A1', 'appendBlockToLastRow', []]
                    )
                ),
            'Test that a CompactBlockWorksheet with multiple blocks added to same row renders correctly' =>
                array(
                    'testSpreadsheetWithMultipleBlocksInSingleRowOnCompactBlockWorksheet',
                    array(
                        ['B2', 'B2', 'appendBlockToLastRow', []],
                        ['C4', 'E4', 'appendBlockToLastRow', []],
                        ['A1', 'F1', 'appendBlockToLastRow', []]
                    )
                ),
            'Test that a CompactBlockWorksheet with multiple blocks added as separate rows renders correctly' =>
                array(
                    'testSpreadsheetWithMultipleBlocksInSingleColumnOnCompactBlockWorksheet',
                    array(
                        ['B5', 'B5', 'addBlockAsRow', []],
                        ['C4', 'C9', 'addBlockAsRow', []],
                        ['A3', 'A12', 'addBlockAsRow', []]
                    )
                ),
            'Test that a CompactBlockWorksheet with multiple blocks added as rows and columns renders correctly, also test the CompactBlockWorksheet::appendBlockToRow method' =>
                array(
                    'testSpreadsheetWithMultipleRowsAndColumnsOnCompactBlockWorksheet',
                    array(
                        ['B5', 'B5', 'addBlockAsRow', []],
                        ['C4', 'C9', 'addBlockAsRow', []],
                        ['A3', 'C3', 'appendBlockToRow', [1]],
                        ['D2', 'G7', 'appendBlockToRow', [2]]
                    )
                ),
            'Test that the CompactBlockWorksheet::insertBlockAfterRow inserts row correctly' =>
                array(
                    'testInsertBlockAfterRowWhenSuppliedRowIsBetweenTwoRows',
                    array(
                        ['B2', 'B2', 'addBlockAsRow', []],
                        ['C3', 'C9', 'addBlockAsRow', []],
                        ['D4', 'D6', 'insertBlockAfterRow', [1]]
                    )
                ),
            'Test that insertBlockAfterRow inserts row as first row the supplied row number is zero' =>
                array(
                    'testInserBlockAfterRowWhenSuppliedRowIsZero',
                    array(
                        ['C1', 'C5', 'addBlockAsRow', []],
                        ['E3', 'E8', 'addBlockAsRow', []],
                        ['B4', 'B4', 'insertBlockAfterRow', [0]]
                    )
                ),
            'Test that insertBlockAfterRow inserts row as first row the supplied row number is last row number' =>
                array(
                    'testInsertBlockAfterRowWhenSuppliedRowIsLastRow',
                    array(
                        ['E9', 'E9', 'addBlockAsRow', []],
                        ['D4', 'D13', 'addBlockAsRow', []],
                        ['C7', 'C20', 'insertBlockAfterRow', [2]]
                    )
                ),
            'Test that the CompactBlockWorksheet::insertBlockBeforeRow inserts row correctly' =>
                array(
                    'testInsertBlockBeforeRowWhenSuppliedRowIsBetweenTwoRows',
                    array(
                        ['B2', 'B2', 'addBlockAsRow', []],
                        ['C3', 'C9', 'addBlockAsRow', []],
                        ['D4', 'D6', 'insertBlockBeforeRow', [2]]
                    )
                ),
            'Test that insertBlockBeforeRow inserts row as first row the supplied row number is one' =>
                array(
                    'testInsertBlockBeforeRowWhenSuppliedRowIsOne',
                    array(
                        ['C1', 'C5', 'addBlockAsRow', []],
                        ['E3', 'E8', 'addBlockAsRow', []],
                        ['B4', 'B4', 'insertBlockBeforeRow', [1]]
                    )
                ),
            'Test that insertBlockAfterColumn works when inserting a block between two columns' =>
                array(
                    'testInsertBlockAfterColumnWhenSuppliedColumnIsBetweenTwoColumns',
                    array(
                        ['A4', 'A4', 'appendBlockToLastRow', []],
                        ['C6', 'F6', 'appendBlockToLastRow', []],
                        ['B4', 'C4', 'insertBlockAfterColumn', [1, 1]]
                    )
                ),
            'Test that insertBlockAfterColumn adds block as first block in row when column number is zero' =>
                array(
                    'testInsertBlockAfterColumnWhenSuppliedColumnNumberIsZero',
                    array(
                        ['F7', 'H7', 'appendBlockToLastRow', []],
                        ['D12', 'L12', 'appendBlockToLastRow', []],
                        ['B8', 'B8', 'insertBlockAfterColumn', [1, 0]]
                    )
                ),
            'Test that insertBlockAfterColumn adds block as last block in row when column number is the last column' =>
                array(
                    'testInsertBlockAfterColumnWhenSuppliedColumnNumberIsLastColumn',
                    array(
                        ['C2', 'C2', 'appendBlockToLastRow', []],
                        ['A4', 'D4', 'appendBlockToLastRow', []],
                        ['C1', 'G1', 'insertBlockAfterColumn', [1, 2]]
                    )
                ),
            'Test that insertBlockBeforeColumn works when inserting a block between two columns' =>
                array(
                    'testInsertBlockBeforeColumnWhenSuppliedColumnIsBetweenTwoColumns',
                    array(
                        ['A4', 'A4', 'appendBlockToLastRow', []],
                        ['C6', 'F6', 'appendBlockToLastRow', []],
                        ['B4', 'C4', 'insertBlockBeforeColumn', [1, 2]]
                    )
                ),
            'Test that insertBlockBeforeColumn adds block as first block in row when column number is one' =>
                array(
                    'testInsertBlockBeforeColumnWhenSuppliedColumnNumberIsOne',
                    array(
                        ['G6', 'H6', 'appendBlockToLastRow', []],
                        ['B2', 'J2', 'appendBlockToLastRow', []],
                        ['A2', 'A2', 'insertBlockBeforeColumn', [1, 1]]
                    )
                ),
        );
    }

    /**
     * Test creating dynamic blocks and adding them to worksheets.
     * This test will
     * - create a set of blocks
     * - add them to a CompactBlockWorksheet and Spreedsheet
     * - write the Spreedsheet to disk
     * - read the Spreadsheet back into memory
     * - test that all cells are populated with the expected results
     *
     * @dataProvider blockWorksheetInsertionDataProvider
     *
     * @param  string $file_name    Filename to save the worksheet as
     * @param  array  $cell_mapping An array of arrays where each array represents a block and has four parameters:
     *     [
     *       $insert_coordinate   - A unique value will be inserted into this block at this coordinate
     *       $expected_coordinate - The expected location on the final Spreedsheet that should correspond with the unique value
     *       $block_method        - The method that should be used to add the block to the CompactBlockWorksheet
     *       $extra_parameters    - Extra parameters to pass to the $block_method
     *     ]
     *
     * @return void
     */
    public function testWorksheetBlockInsertionMethods($file_name, $cell_mapping)
    {
        $compact_block_worksheet = new CompactBlockWorksheet();
        $count = 0;
        $expected_coordinate_values = array();
        foreach ($cell_mapping as list($insert_coordinate, $expected_coordinate, $block_method, $extra_parameters)) {
            ++$count;
            $value = "block ${count}";
            $dynamic_block = new DynamicBlock();
            $dynamic_block->addCell($insert_coordinate, $value);

            $compact_block_worksheet->$block_method($dynamic_block, ...$extra_parameters);

            $expected_coordinate_values[$expected_coordinate] = $value;
        }
        $this->assertBlockWorksheetProducesExepectedResults($compact_block_worksheet, $expected_coordinate_values, $file_name . '.xlsx');
    }

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

        $missing_cells = array();
        $wrong_value_cells = array();
        $extra_coordinates = array();

        // Check that all coordinates match our expected values
        foreach ($expected_coordinate_values as $coordinate => $expected_value) {
            if (!$rendered_cells->has($coordinate)) {
                $missing_cells[$coordinate] = $expected_value;
            } elseif (($actual_value = $rendered_cells->get($coordinate)->getValue()) != $expected_value) {
                $wrong_value_cells[$coordinate] = ['expected' => $expected_value, 'actual' => $actual_value];
            }
        }

        // Check if there are any unexpected coordinates
        foreach (array_diff($rendered_cells->getCoordinates(), array_keys($expected_coordinate_values)) as $extra_coordinate) {
            $extra_coordinates[$extra_coordinate] = $rendered_cells->get($extra_coordinate)->getValue();
        }

        if ($missing_cells || $wrong_value_cells || $extra_coordinates) {
            $error_message = 'Rendered XLSX file did not match expected results:' . PHP_EOL;
            foreach ($missing_cells as $coordinate => $value) {
                $error_message .= '  -  ';
                $error_message .= "Missing expected value '${value}' at coordinate ${coordinate}" . PHP_EOL;
            }
            foreach ($wrong_value_cells as $coordinate => $values) {
                $error_message .= '  -  ';
                $error_message .= "Expected value '${values['expected']}' for coordinate ${coordinate} but actual value was '${values['actual']}'" . PHP_EOL;
            }
            foreach ($extra_coordinates as $coordinate => $value) {
                $error_message .= '  -  ';
                $error_message .= "Expected coordinate ${coordinate} to not be set but had value of '${value}'" . PHP_EOL;
            }
            $error_message .= "Failed XLSX file viewable at: '${file_location}'";
            $this->fail($error_message);
        }

        unlink($file_location);
        $this->assertTrue(true);
    }
}
