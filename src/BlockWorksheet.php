<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BlockWorksheet extends Worksheet
{
    /**
     * Clear all cellls from worksheet
     *
     * @return void
     */
    protected function clearCells()
    {
        $cells = $this->getCellCollection();
        foreach ($cells->getCoordinates() as $coordinate) {
            $cells->delete($coordinate);
        }
    }
}
