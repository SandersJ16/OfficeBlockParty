<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

interface Block
{
    /**
     * Get the height of this XlsxBlock
     *
     * @return int
     */
    public function getHeight();

    /**
     * Get the width of this XlsxBlock
     *
     * @return int
     */
    public function getWidth();

    /**
     * Get a cell from this block
     *
     * @param  string $coordinate The cell's coordinate relative to this block
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell;
     */
    public function getCell($coordinate) : Cell;
}
