<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DynamicBlock implements Block
{
    /**
     * The cells that will be rendered by this block,
     * it's a multidimensional array with the row index
     * as the first key and the column index as the second
     * key (both column and key indexes start at 1)
     *
     * @var array
     */
    private $cells = array();

    /**
     * Get the height of this XlsxBlock
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->cells ? max(array_keys($this->cells)) : 0;
    }

    /**
     * Get the width of this XlsxBlock
     *
     * @return int
     */
    public function getWidth()
    {
        $max_cell_width = 0;
        foreach ($this->cells as $cell_row) {
            $max_row_column = max(array_keys($cell_row));
            $max_cell_width = max($max_row_column, $max_cell_width);
        }
        return $max_cell_width;
    }

    /**
     * Add a cell to this block
     *
     * @param  string $cell The cell's name
     * @param  mixed $data
     *
     * @return self
     */
    public function addCell($cell, $data)
    {
        list($row, $column) = Coordinate::coordinateFromString($cell);
        $row = Coordinate::columnIndexFromString($row);
        $this->cells[$row][$column] = $data;

        return $this;
    }

    public function getCellData($cell)
    {
        list($row, $column) = Coordinate::coordinateFromString($cell);
        $row = Coordinate::columnIndexFromString($row);
        return $this->cells[$row][$column];
    }
}
