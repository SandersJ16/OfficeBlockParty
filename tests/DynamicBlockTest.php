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
    public function testGetWidthOnEmptyBlock() {
        $block = new DynamicBlock();
        $this->assertEquals(0, $block->getWidth());
    }
}
