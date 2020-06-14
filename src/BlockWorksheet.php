<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BlockWorksheet extends Worksheet
{
    protected function clearCells() {
        $cells = $this->getCellCollection();
        foreach ($cells->getCoordinates() as $coordinate) {
            $cells->delete($coordinate);
        }
    }
}
