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
    public function appendBlockToLastRow(Block $block)
    {
        $last_key = count($this->block_rows);
        $last_key = $last_key ?: 1; // If $last_key is 0 then set it as 1 to append to 1st row
        $this->appendBlockToRow($block, $last_key);

        return $this;
    }

    /**
     * Add a block as a new column to a specific row number on this worksheet
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function appendBlockToRow(Block $block, int $row_number)
    {
        --$row_number;
        if ($row_number < 0) {
            throw new \InvalidArgumentException("Row ${row_number} invalid, supplied row must be larger than 0");
        } elseif ($row_number && !isset($this->block_rows[$row_number])) {
            throw new \InvalidArgumentException("Row ${row_number} doesn't exist");
        }
        $this->block_rows[$row_number][] = $block;
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Insert a block as a new row after an existing row
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function insertBlockAfterRow(Block $block, $row_number)
    {
        if ($row_number < 0) {
            throw new \InvalidArgumentException("Row ${row_number} invalid, supplied row must be larger than 0");
        } elseif ($row_number > count($this->block_rows)) {
            throw new \InvalidArgumentException("Row ${row_number} doesn't exist");
        }
        $this->block_rows = $this->insertIntoArrayAfterIndex([$block], $this->block_rows, $row_number);
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

    /**
     * Insert data into an array after a specific index
     *
     * @param  mixed $data
     * @param  array $array
     * @param  int   $index
     *
     * @return array
     */
    private function insertIntoArrayAfterIndex($data, array $array, $index)
    {
        return array_merge(
            array_slice($array, 0, $index),
            array($data),
            array_slice($array, $index, count($array))
        );
    }
}
