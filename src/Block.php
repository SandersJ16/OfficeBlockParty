<?php

namespace OfficeBlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

interface Block
{
    /**
     * Get the height of this Block.
     *
     * @return int
     */
    public function getHeight();

    /**
     * Get the width of this Block.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Get a cell from this block.
     *
     * @param  string $coordinate The cell's coordinate relative to this block.
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell;
     */
    public function getCell($coordinate) : Cell;

    /**
     * Get the current cell coordinates for all data in this block relative to this block.
     *
     * @return array
     */
    public function getRelativeCoordinates();
}
