<?php

namespace OfficeBlockParty;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BlockWorksheet extends Worksheet
{
    /**
     * Clear all cells from worksheet.
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
