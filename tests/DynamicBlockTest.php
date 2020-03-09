<?php

namespace BlockParty\Test;

use BlockParty\DynamicBlock;
use PHPUnit\Framework\TestCase;
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
}
