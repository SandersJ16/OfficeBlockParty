<?php

namespace OfficeBlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


interface GridBlockWorksheet
{
    /**
     * Add a block as the first block in a new row on this worksheet
     *
     * @param  Block $block
     *
     * @return self
     */
    public function addBlockAsRow(Block $block);

    /**
     * Add a block as a new column on the current last block row of this worksheet
     *
     * @param  Block $block
     *
     * @return self
     */
    public function appendBlockToLastRow(Block $block);

    /**
     * Add a block as a new column to a specific row number on this worksheet
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function appendBlockToRow(Block $block, int $row_number);

    /**
     * Insert a block as a new row after an existing row
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function insertBlockAfterRow(Block $block, $row_number);

    /**
     * Insert a block as a new row before an existing row
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function insertBlockBeforeRow(Block $block, $row_number);

    /**
     * Insert a block into a row after an existing column
     *
     * @param  Block $block
     * @param  int   $row_number
     * @param  int   $column_number
     *
     * @return self
     */
    public function insertBlockAfterColumn(Block $block, $row_number, $column_number);

    /**
     * Insert a block into a row before an existing column
     *
     * @param  Block  $block
     * @param  int    $row_number
     * @param  int    $column_number
     *
     * @return self
     */
    public function insertBlockBeforeColumn(Block $block, $row_number, $column_number);
}
