<?php

namespace BlockParty\Test;

use BlockParty\DynamicBlock;
use PHPUnit\Framework\TestCase;

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
     * Data provider for testing DynamicExelBlock::getHeight, see DynamicBlockTest::testGetHeight for expected data provider values
     *
     * @return array
     */
    public function getHeightDataProvider()
    {
        return [
            'Test adding a cell with a null value still affects height' =>
                ['addCell', ['E1', null], 5],
            'Test adding a cell to the first row sets height to 1' =>
                ['addCell', ['A1', 'test'], 1],
            "Test adding a cell to any row greater than 1 sets the height to that cell's row" =>
                ['addCell', ['AX1', 'test'], 50],
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
}
