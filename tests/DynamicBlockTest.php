<?php

namespace BlockParty\Test;

use BlockParty\DynamicBlock;
use PHPUnit\Framework\TestCase;

final class DynamicBlockTest extends TestCase
{
    /**
     * Test the DynamicBlockTest::getHeight function when no cells have been added
     *
     * @group xlsx
     *
     * @return void
     */
    public function testGetHeightOnEmptyBlock()
    {
        $block = new DynamicBlock();
        $this->assertEquals(0, $block->getHeight());
    }
}
