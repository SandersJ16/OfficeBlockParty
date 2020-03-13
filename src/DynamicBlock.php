<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
     * This will be the worksheet that new cells are initilized with.
     * The Cell class requires a Worksheet in order to be initilized
     * so we give each block its own worksheet to hold the cells.
     * This worksheet is only to keep track of the cells, it will
     * not be added to the actual spreadsheet
     *
     * @var PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    private $temp_worksheet;

    public function __construct()
    {
        $this->temp_worksheet = new Worksheet();
    }

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
    public static function getSizedBlock(int $height, int $width) : self
    {
        if ($height <= 0) {
            throw new CellOutOfBlockException(sprintf("Sized Block has a minium height of 1, '%i' specified", $height));
        } elseif ($width <= 0) {
            throw new CellOutOfBlockException(sprintf("Sized Block has a minium width of 1, '%i' specified", $width));
        }

        $block = new self();
        $column_letter = Coordinate::stringFromColumnIndex($height);
        $bottom_corner_coordinate = $column_letter . $width;
        $block->addCell($bottom_corner_coordinate, null);
        return $block;
    }

    /**
     * Get the width of this XlsxBlock
     *
     * @return int
     */
    public function getWidth() : int
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
     * @param  string  $coordinate The cell's coordinate relative to this block
     * @param  mixed   $data
     * @param  ?string $data_type  The data type of the new cell
     *                             (see DataType class constants for valid values)
     *
     * @return self
     */
    public function addCell($coordinate, $data, $data_type = null) : self
    {
        list($row, $column) = Coordinate::coordinateFromString($coordinate);
        $row = Coordinate::columnIndexFromString($row);
        $this->cells[$row][$column] = $this->createNewCell($data, $data_type);

        return $this;
    }

    /**
     * Create a new Cell to be used in this block
     *
     * @param  mixed  $data      The data to set as this cell's value
     * @param  string $data_type The data type of this sell
     *                           (see DataType class constants for valid values)
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    private function createNewCell($data, $data_type) : Cell
    {
        if (empty($data_type)) {
            $data_type = DataType::TYPE_NULL;
        }
        $cell = new Cell($data, $data_type, $this->temp_worksheet);
        return $cell;
    }

    /**
     * Get the data stored in a cell in this block
     *
     * @param  string $coordinate The cell's coordinate relative to this block
     *
     * @return string
     */
    public function getCellData($coordinate)
    {
        $cell = $this->getCell($coordinate);
        return $cell->getValue();
    }

    /**
     * Get a cell from this block
     *
     * @param  string $coordinate The cell's coordinate relative to this block
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell;
     */
    public function getCell($coordinate) : Cell
    {
        list($row, $column) = Coordinate::coordinateFromString($coordinate);
        $row = Coordinate::columnIndexFromString($row);

        if ($row > $this->getHeight() || $column > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("Cell '%s' is out of range the block", $coordinate));
        } elseif (!isset($this->cells[$row][$column])) {
            $this->addCell($coordinate, null);
        }
        return $this->cells[$row][$column];
    }
}
