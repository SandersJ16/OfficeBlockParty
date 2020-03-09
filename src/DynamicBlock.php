<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use BlockParty\Exceptions\CellOutOfBlockException;

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
     * Get an empty block that already has a specific height and width
     *
     * @param  int    $height
     * @param  int    $width
     *
     * @return self
     */
    public static function getSizedBlock(int $height, int $width)
    {
        if ($height <= 0) {
            throw new CellOutOfBlockException(sprintf("Sized Block has a minium height of 1, '%i' specified", $height));
        } elseif ($width <= 0) {
            throw new CellOutOfBlockException(sprintf("Sized Block has a minium width of 1, '%i' specified", $width));
        }

        $block = new self();
        $column_letter = Coordinate::stringFromColumnIndex($height);
        $bottom_corner_cell = $column_letter . $width;
        $block->addCell($bottom_corner_cell, null);
        return $block;
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

    /**
     * Get the data stored in a cell in this block
     *
     * @param  string $cell The cell's name
     *
     * @return string
     */
    public function getCellData($cell)
    {
        list($row, $column) = Coordinate::coordinateFromString($cell);
        $row = Coordinate::columnIndexFromString($row);

        if ($row > $this->getHeight() || $column > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("Cell '%s' is out of range the block", $cell));
        }
        return $this->cells[$row][$column];
    }
}
