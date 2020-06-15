<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CompactBlockWorksheet extends BlockWorksheet
{
    /**
     * Rows of blocks to be applied to the worksheet
     *
     * @var array
     */
    protected $block_rows = array();

    /**
     * Add a block as the first block in a new row on this worksheet
     *
     * @param  Block $block
     *
     * @return self
     */
    public function addBlockAsRow(Block $block)
    {
        $this->block_rows[] = array($block);
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Add a block as a new column on the current last block row of this worksheet
     *
     * @param  Block $block
     *
     * @return self
     */
    public function appendBlockAsColumn(Block $block)
    {
        $last_key = count($this->block_rows);
        $this->appendBlockToColumn($block, $last_key);
        return $this;
    }

    /**
     * Add a block as a new column to a specific row number on this worksheet
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return XlsxBlockWorksheet
     */
    public function appendBlockToColumn(Block $block, int $row_number)
    {
        $this->block_rows[$row_number - 1][] = $block;
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Populate this worksheets cells from rows of blocks
     *
     * @param  $block_rows
     *
     * @return void
     */
    protected function populateCellsFromBlocks($block_rows)
    {
        $this->clearCells();

        $vertical_translation = 0;
        foreach ($block_rows as $rows) {
            $horizontal_translation = 0;
            $row_max_cell_height = 0;
            foreach ($rows as $block) {
                $relative_coordinates = $block->getRelativeCellCoordinates();
                foreach ($relative_coordinates as $coordinate) {
                    $coordinate_parts = Coordinate::coordinateFromString($coordinate);
                    $column_coordinate = Coordinate::columnIndexFromString($coordinate_parts[0]) + $horizontal_translation;
                    $row_coordinate = $coordinate_parts[1] + $vertical_translation;
                    $this->setCellValueByColumnAndRow($column_coordinate, $row_coordinate, $block->getCellData($coordinate));
                }

                $horizontal_translation += $block->getWidth();
                if ($block->getHeight() > $row_max_cell_height) {
                    $row_max_cell_height = $block->getHeight();
                }
            }
            $vertical_translation += $row_max_cell_height;
        }
    }
}
