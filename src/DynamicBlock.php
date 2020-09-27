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
     * This will be the worksheet that the block's cell's configurations
     * will be tracked with. This worksheet is only to keep track of the
     * cell's configuration, it will not be directly added to any spreadsheet.
     *
     * @var PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    private $internal_worksheet;

    public function __construct()
    {
        $this->internal_worksheet = new Worksheet();
    }

    /**
     * Get an empty block that already has a specific height and width.
     *
     * @param  int $height
     * @param  int $width
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

        $block = new static();
        $column_letter = Coordinate::stringFromColumnIndex($width);
        $bottom_corner_coordinate = $column_letter . $height;
        $block->setCellValue($bottom_corner_coordinate, null);
        return $block;
    }

    /**
     * Get the height of this Block.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->internal_worksheet->getHighestRow();
    }

    /**
     * Get the width of this Block.
     *
     * @return int
     */
    public function getWidth() : int
    {
        $highest_column = $this->internal_worksheet->getHighestColumn();
        return Coordinate::columnIndexFromString($highest_column);
    }

    /**
     * Return if a cell is currently in the block.
     *
     * @param  string $coordinate  The cell's coordinate relative to this block.
     *
     * @return bool
     */
    public function cellInBlock($coordinate)
    {
        list($column, $row) = Coordinate::coordinateFromString($coordinate);
        $column_index = Coordinate::columnIndexFromString($column);
        return $this->cellInBlockByColumnAndRow($column_index, $row);
    }

    /**
     * Return if a cell is currently in the block by its column index and row number.
     *
     * @param  int $column_index  The cell's column index relative to this block.
     * @param  int $row           The cell's row number relative to this block.
     *
     * @return bool
     */
    public function cellInBlockByColumnAndRow($column_index, $row)
    {
        return $column_index > 0 && $column_index <= $this->getWidth() && $row > 0 && $row <= $this->getHeight();
    }

    /**
     * Get the value stored in a cell in this block.
     *
     * @param  string $coordinate  The cell's coordinate relative to this block.
     *
     * @return mixed
     */
    public function getCellValue($coordinate)
    {
        return $this->getCell($coordinate)->getValue();
    }

    /**
     * Get a cell from this block.
     *
     * @param  string $coordinate  The cell's coordinate relative to this block.
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell
     *
     * @throws OfficeBlockParty\Exceptions\CellOutOfBlockException Thrown when the requested coordinate
     *                                                             doesn't currently exist in the cell.
     */
    public function getCell($coordinate) : Cell
    {
        list($column, $row) = Coordinate::coordinateFromString($coordinate);
        $column_index = Coordinate::columnIndexFromString($column);

        if ($row > $this->getHeight() || $column_index > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("Cell '%s' is out of range the block", $coordinate));
        }
        return $this->internal_worksheet->getCell($coordinate, true);
    }

    /**
     * Get a cell from this block by its column index and row number.
     *
     * @param  int $column_index  The cell's column index relative to this block.
     * @param  int $row           The cell's row number relative to this block.
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell
     *
     * @throws OfficeBlockParty\Exceptions\CellOutOfBlockException Thrown when the requested coordinate
     *                                                             doesn't currently exist in the cell
     */
    public function getCellByColumnAndRow($column_index, $row) : Cell
    {
        if ($row > $this->getHeight() || $column_index > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("Cell coordinates (%i, %i) is out of range the block", $column_index, $row));
        }
        return $this->internal_worksheet->getCellByColumnAndRow($column_index, $row, true);
    }

    /**
     * Get the current cell coordinates for all data in this block relative to this block.
     *
     * @return array
     */
    public function getRelativeCoordinates()
    {
        return $this->internal_worksheet->getCoordinates(true);
    }

    /**
     * Gets the rightmost column of the block.
     *
     * @param  ?int $row  Return the rightmost column for the specified row, or
     *                    the rightmost column of any row if no row number is passed.
     *
     * @return string
     */
    public function getHighestColumn($row = null)
    {
        if (!is_null($row) && ($row > $this->getHeight() || $row <= 0)) {
            throw new CellOutOfBlockException(sprintf("The specified row '%i' is out of range of the block", $row));
        }
        return $this->internal_worksheet->getHighestColumn($row);
    }

    /**
     * Gets the highest row number of the block.
     *
     * @param  ?string $column  Returns the highest row number for the specified column or
     *                          the highest row number of any column if no column is passed.
     *
     * @return int
     */
    public function getHighestRow($column = null)
    {
        if (!is_null($column) && Coordinate::columnIndexFromString($column) > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("The specified column '%s' is out of range of the block", $column));
        }
        return $this->internal_worksheet->getHighestRow($column) ?: 1; //Force 1 if has a value of 0
    }

    /**
     * Set the value of a cell inside the block. If the coordinate specified exists
     * outside of the block, the blocks size will grow to accommodate the new cell.
     *
     * @param  string  $coordinate
     * @param  mixed   $value
     * @param  ?string $data_type   The data type of the new cell
     *                              (see DataType class constants for valid values).
     *
     * @return self
     */
    public function setCellValue($coordinate, $value, $data_type = null)
    {
        if ($data_type) {
            $this->internal_worksheet->setCellValueExplicit($coordinate, $value, $data_type);
        } else {
            $this->internal_worksheet->setCellValue($coordinate, $value);
        }
        return $this;
    }

    /**
     * Set the value of a cell inside the block using a column index and row number.
     * If the cell specified by the column index and row number exists outside of
     * the block, the blocks size will grow to accommodate the new cell.
     *
     * @param  int     $column_index Numeric column coordinate of the cell.
     * @param  int     $row          Numeric row coordinate of the cell.
     * @param  mixed   $value        Value to set in the cell.
     * @param  ?string $data_type    The data type of the new cell
     *                               (see DataType class constants for valid values).
     *
     * @return Worksheet
     */
    public function setCellValueByColumnAndRow($column_index, $row, $value, $data_type = null)
    {
        if ($data_type) {
            $this->internal_worksheet->setCellValueExplicitByColumnAndRow($column_index, $row, $value, $data_type);
        } else {
            $this->internal_worksheet->setCellValueByColumnAndRow($column_index, $row, $value);
        }
        return $this;
    }
}
