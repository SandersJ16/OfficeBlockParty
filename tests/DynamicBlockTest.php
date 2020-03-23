<?php

namespace BlockParty\Test;

use BlockParty\DynamicBlock;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use BlockParty\Exceptions\CellOutOfBlockException;

final class DynamicBlockTest extends TestCase
{
    /**
     * Test the DynamicBlock::getHeight function when no cells have been added
     *
     * @return void
     */
    public function testGetHeightOnEmptyBlock()
    {
        $block = new DynamicBlock();
        $this->assertEquals(0, $block->getHeight());
    }

    /**
     * Test the DynamicBlock::getWidth function when no cells have been added
     *
     * @return void
     */
    public function testGetWidthOnEmptyBlock()
    {
        $block = new DynamicBlock();
        $this->assertEquals(0, $block->getWidth());
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
                ['addCell', ['E1', null], 5],
            'Test adding a cell to the first row sets height to 1' =>
                ['addCell', ['A3', 'test'], 1],
            "Test adding a cell to any row greater than 1 sets the height to that cell's row" =>
                ['addCell', ['AX7', 'test'], 50],
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
                ['addCell', ['A5', null], 5],
            'Test adding a cell to the first column sets width to 1' =>
                ['addCell', ['C1', 'test'], 1],
            "Test adding a cell to any column greater than 1 sets the width to that cell's row" =>
                ['addCell', ['B50', 'test'], 50],
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
        $block->addCell($cell, $expected_cell_value);

        $cell_value = $block->getCellData($cell);

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
        $block->addCell($cell, $expected_cell_value);

        $cell_value = $block->getCellData($cell);

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
        $block->addCell('B2', null);

        $this->expectException(CellOutOfBlockException::class);
        $cell_value = $block->getCellData('C3');
    }

    /**
     * Test getting cell data from inside of a block that has never had addCell called on it
     *
     * @return void
     */
    public function testGettingCellDataFromCellNeverExpclitilySetButInBlockRange()
    {
        $block = new DynamicBlock();
        $block->addCell('Z20', null);

        $cell_value = $block->getCellData('A1');
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
        $block->addCell('A1', $value, $data_type);
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
     * Test getting a cell from outside of the block throws the appropriate exception
     *
     * @return void
     */
    public function testGettingCellFromOutsideOfBlock()
    {
        $block = new DynamicBlock();
        $block->addCell('B2', null);

        $this->expectException(CellOutOfBlockException::class);
        $cell = $block->getCell('C3');
    }

    /**
     * Test getting a cell inside of a block that has never had addCell called on it
     *
     * @return void
     */
    public function testGettingCellFromCellNeverExpclitilySetButInBlockRange()
    {
        $block = new DynamicBlock();
        $block->addCell('Z20', null);

        $cell = $block->getCell('A1');
        $this->assertInstanceOf(Cell::class, $cell);
        $this->assertEquals(null, $cell->getValue());
    }
}
