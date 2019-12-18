<?php

namespace BlockParty;

use PhpOffice\Spreadsheet;

class BlockSpreadSheet extends Spreadsheet
{
    public function __construct()
    {
        parent::__construct();
        $this->disconnectWorksheets();
        $this->createWorksheet();
    }

    /**
     * Create sheet and add it to this workbook.
     *
     * @param null|int $sheetIndex Index where sheet should go (0,1,..., or null for last)
     *
     * @throws Exception
     *
     * @return Worksheet
     */
    public function createSheet($sheetIndex = null)
    {
        $newSheet = new BlockWorksheet($this);
        $this->addSheet($newSheet, $sheetIndex);

        return $newSheet;
    }
}
