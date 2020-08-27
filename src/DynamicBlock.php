<?php

namespace OfficeBlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Collection\CellsFactory;

use OfficeBlockParty\Exceptions\CellOutOfBlockException;

class DynamicBlock implements Block
{
    /**
     * The cells that will be rendered by this block
     *
     * @var PhpOffice\PhpSpreadsheet\Collection\Cells
     */
    private $cells;

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
        $this->cells = CellsFactory::getInstance($this->temp_worksheet);
    }

    /**
     * Get the height of this XlsxBlock
     *
     * @return int
     */
    public function getHeight()
    {
        return !empty($this->cells->getCoordinates()) ? $this->cells->getHighestRow() : 0;
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
        $column_letter = Coordinate::stringFromColumnIndex($width);
        $bottom_corner_coordinate = $column_letter . $height;
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
        $column = $this->getHighestColumn();
        return isset($column) ? Coordinate::columnIndexFromString($column) : 0;
    }

    /**
     * Gets the rightmost column of the block
     *
     * @return ?string Returns null if there are no cells in the block
     */
    public function getHighestColumn()
    {
        return !empty($this->cells->getCoordinates()) ? $this->cells->getHighestColumn() : null;
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
        $this->cells->add($coordinate, $this->createNewCell($data, $data_type));
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
        list($column, $row) = Coordinate::coordinateFromString($coordinate);
        $column = Coordinate::columnIndexFromString($column);

        if ($row > $this->getHeight() || $column > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("Cell '%s' is out of range the block", $coordinate));
        } elseif (!$this->cells->has($coordinate)) {
            $this->addCell($coordinate, null);
        }
        return $this->cells->get($coordinate);
    }

    /**
     * Get the current cell coordinates for all data in this block relative to this block
     *
     * @return array
     */
    public function getRelativeCellCoordinates()
    {
        return $this->cells->getCoordinates();
    }
}
