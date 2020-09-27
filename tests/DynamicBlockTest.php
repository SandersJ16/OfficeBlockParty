<?php

namespace OfficeBlockParty\Test;

use OfficeBlockParty\DynamicBlock;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use OfficeBlockParty\Exceptions\CellOutOfBlockException;

final class DynamicBlockTest extends OfficeBlockPartyTestCase
{
    /**
     * Test the DynamicBlock::getHeight function when no cells have been added
     *
     * @return void
     */
    public function testGetHeightOnEmptyBlock()
    {
        $block = new DynamicBlock();
        $this->assertEquals(1, $block->getHeight());
    }

    /**
     * Test the DynamicBlock::getWidth function when no cells have been added
     *
     * @return void
     */
    public function testGetWidthOnEmptyBlock()
    {
        $block = new DynamicBlock();
        $this->assertEquals(1, $block->getWidth());
    }


    /**
     * Data provider for testing DynamicBlock::getHeight, see DynamicBlockTest::testGetHeight for expected data provider values
     *
     * @return array
     */
    public function getHeightDataProvider()
    {
        return [
            'Test adding a cell with a null value still affects height' =>
                ['setCellValue', ['E5', null], 5],
            'Test adding a cell to the first row sets height to 1' =>
                ['setCellValue', ['Q1', 'test'], 1],
            "Test adding a cell to any row greater than 1 sets the height to that cell's row" =>
                ['setCellValue', ['AX70', 'test'], 70],
        ];
    }

    /**
     * Test DynamicBlock::getHeight for all functions that can modify a blocks height
     *
     * @dataProvider getHeightDataProvider
     *
     * @param  string $testing_function   Name of the function to call
     * @param  array  $function_arguments Arguments to pass to the unction
     * @param  int    $expected_height    The expected height of the block after the function call
     *
     * @return void
     */
    public function testGetHeight($testing_function, $function_arguments, $expected_height)
    {
        $block = new DynamicBlock();
        $block->$testing_function(...$function_arguments);
        $this->assertEquals($expected_height, $block->getHeight());
    }

    /**
     * Data provider for testing DynamicBlock::getWidth, see DynamicBlockTest::testGetWidth for expected data provider values
     *
     * @return array
     */
    public function getWidthDataProvider()
    {
        return [
            'Test adding a cell with a null value still affects width' =>
                ['setCellValue', ['D3', null], 4],
            'Test adding a cell to the first column sets width to 1' =>
                ['setCellValue', ['A12', 'test'], 1],
            "Test adding a cell to any column after A sets the width to that cell's row" =>
                ['setCellValue', ['AX10', 'test'], 50],
        ];
    }

    /**
     * Test DynamicBlock::getWidth for all functions that can modify a blocks width
     *
     * @dataProvider getWidthDataProvider
     *
     * @param  string $testing_function   Name of the function to call
     * @param  array  $function_arguments Arguments to pass to the unction
     * @param  int    $expected_width     The expected width of the block after the function call
     *
     * @return void
     */
    public function testGetWidth($testing_function, $function_arguments, $expected_width)
    {
        $block = new DynamicBlock();
        $block->$testing_function(...$function_arguments);
        $this->assertEquals($expected_width, $block->getWidth());
    }

    /**
     * Test adding a cell with no data returns null
     *
     * @return void
     */
    public function testAddingEmptyCell()
    {
        $cell = "BA2";
        $expected_cell_value = null;

        $block = new DynamicBlock();
        $block->setCellValue($cell, $expected_cell_value);

        $cell_value = $block->getCellValue($cell);

        $this->assertEquals($expected_cell_value, $cell_value);
    }

    /**
     * Test adding a cell to a block with a text value returns that text
     *
     * @return void
     */
    public function testAddingCellWithOnlyText()
    {
        $cell = "D12";
        $expected_cell_value = 'test';

        $block = new DynamicBlock();
        $block->setCellValue($cell, $expected_cell_value);

        $cell_value = $block->getCellValue($cell);

        $this->assertEquals($expected_cell_value, $cell_value);
    }

    /**
     * Test getting cell data from outside of the block throws the appropriate exception
     *
     * @return void
     */
    public function testGettingCellDataFromOutsideOfBlock()
    {
        $block = new DynamicBlock();
        $block->setCellValue('B2', null);

        $this->expectException(CellOutOfBlockException::class);
        $cell_value = $block->getCellValue('C3');
    }

    /**
     * Test getting cell data from inside of a block that has never had setCellValue called on it
     *
     * @return void
     */
    public function testGettingCellDataFromCellNeverExpclitilySetButInBlockRange()
    {
        $block = new DynamicBlock();
        $block->setCellValue('Z20', null);

        $cell_value = $block->getCellValue('A1');
        $this->assertEquals(null, $cell_value);
    }

    /**
     * Data Provider for testing DynamicBlock::getSizedBlock method
     *
     * @return array
     */
    public function getSizedBlockDataProvider()
    {
        return [
            'Test getting a sized block with a valid height and width' =>
                [45, 32, null],
            'Test getting a sized block with a height of 0 throws an exception' =>
                [0, 3, CellOutOfBlockException::class],
            'Test getting a sized block with a width of 0 throws an exception' =>
                [10, 0, CellOutOfBlockException::class],
            'Test getting a sized block with a negative height throws an exception' =>
                [-12, 3, CellOutOfBlockException::class],
            'Test getting a sized block with a negative width throws an exception' =>
                [15, -4, CellOutOfBlockException::class]
        ];
    }

    /**
     * Test the getSizedBlock method
     *
     * @dataProvider getSizedBlockDataProvider
     *
     * @param  int     $height                   Height of the sized block requested
     * @param  int     $width                    Width of the sized block requested
     * @param  ?string $expected_exception_class The class of the exception that should be thrown,
     *                                           null if no exception should be thrown
     *
     * @return void
     */
    public function testGettingSizedBlock(int $height, int $width, ?string $expected_exception_class)
    {
        if ($expected_exception_class) {
            $this->expectException($expected_exception_class);
        }
        $block = DynamicBlock::getSizedBlock($height, $width);
        if (!$expected_exception_class) {
            $this->assertEquals($width, $block->getWidth());
            $this->assertEquals($height, $block->getHeight());
        }
    }

    /**
     * Test that DynamicBlock::getCell returns an instance of PhpOffice\PhpSpreadsheet\Cell\Cell
     *
     * @return array<string, string, Cell>
     */
    public function testGetCellReturnsAPHPSpreadsheetCell()
    {
        $value = 'Test Value';
        $data_type = DataType::TYPE_STRING;

        $block = new DynamicBlock();
        $block->setCellValue('A1', $value, $data_type);
        $cell = $block->getCell('A1');

        $this->assertInstanceOf(Cell::class, $cell);
        return ['value'     => $value,
                'data_type' => $data_type,
                'cell'      => $cell];
    }

    /**
     * Test that DynamicBlock::getCell returns a cell with the expected value
     *
     * @depends testGetCellReturnsAPHPSpreadsheetCell
     *
     * @param  array $arguments     An array containing the cell returned by the method,
     *                              expected value and expected data type
     *
     * @return void
     */
    public function testTheCellReturnedByGetCellContainsTheRightValue(array $arguments)
    {
        $value = $arguments['value'];
        $cell = $arguments['cell'];
        $this->assertEquals($value, $cell->getValue());
    }

    /**
     * Test that DynamicBlock::getCell returns a cell with the expected data type
     *
     * @depends testGetCellReturnsAPHPSpreadsheetCell
     *
     * @param  array $arguments     An array containing the cell returned by the method,
     *                              expected value and expected data type
     *
     * @return void
     */
    public function testTheCellReturnedByGetCellContainsTheRightDataType(array $arguments)
    {
        $data_type = $arguments['data_type'];
        $cell = $arguments['cell'];
        $this->assertEquals($data_type, $cell->getDataType());
    }

    /**
     * Test that DynamicBlock::getHighestColumn returns 'A' when called on an empty block
     *
     * @return void
     */
    public function testGetHighestColumnOnEmptyBlock()
    {
        $block = new DynamicBlock();
        $this->assertEquals('A', $block->getHighestColumn());
    }

    /**
     * Test that DynamicBlock::getHighestColumn returns 'A' when
     * called on a block that only has values in the first column
     *
     * @return void
     */
    public function testGetHighestColumnOnBlockWithValueInFirstColumn()
    {
        $block = new DynamicBlock();
        $block->setCellValue('A5', null);
        $this->assertEquals('A', $block->getHighestColumn());
    }

    /**
     * Test that DynamicBlock::getHighestColumn returns the correct
     * column when called on a block that has values in columns
     * other than the first column
     *
     * @return void
     */
    public function testGetHighestColumnOnBlockWithValueInColumnOtherThanFirst()
    {
        $block = new DynamicBlock();
        $block->setCellValue('E4', null);
        $this->assertEquals('E', $block->getHighestColumn());
    }

    /**
     * Test that DynamicBlock::getHighestColumn returns 'A'
     * when called on a specific row that has no values set
     *
     * @return void
     */
    public function testGetHighestColumnForSpecificRowThatHasNoValues()
    {
        $block = new DynamicBlock();
        $block->setCellValue('B7', null);
        $this->assertEquals('A', $block->getHighestColumn(2));
    }

    /**
     * Test that DynamicBlock::getHighestColumn returns the correct value
     * when called on a specific row that has values in columns other
     * than the first column
     *
     * @return void
     */
    public function testGetHighestColumnForSpecificRowThatHasValuesInColumnOtherThanFirst()
    {
        $block = new DynamicBlock();
        $block->setCellValue('B7', null);
        $this->assertEquals('B', $block->getHighestColumn(7));
    }

    /**
     * Test that calling DynamicBlock::getHighestColumn with a row number
     * outside of the block throws the appropriate exception
     *
     * @return void
     */
    public function testGetHighestColumnForSpecificRowOutsideOfBlock()
    {
        $block = new DynamicBlock();
        $this->expectException(CellOutOfBlockException::class);
        $block->getHighestColumn(16);
    }

    /**
     * Test that calling DynamicBlock::getHighestColumn with a
     * negative row number throws the appropriate exception
     *
     * @return void
     */
    public function testGetHighestColumnForNegativeRowValue()
    {
        $block = new DynamicBlock();
        $this->expectException(CellOutOfBlockException::class);
        $block->getHighestColumn(-3);
    }
}
