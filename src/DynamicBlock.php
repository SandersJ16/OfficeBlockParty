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
     * This will be the worksheet that new cells are initilized with.
     * The Cell class requires a Worksheet in order to be initilized
     * so we give each block its own worksheet to hold the cells.
     * This worksheet is only to keep track of the cells, it will
     * not be added to the actual spreadsheet
     *
     * @var PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    private $internal_worksheet;

    public function __construct()
    {
        $this->internal_worksheet = new Worksheet();
    }

    /**
     * Get the height of this XlsxBlock
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->internal_worksheet->getHighestRow();
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

        $block = new static();
        $column_letter = Coordinate::stringFromColumnIndex($width);
        $bottom_corner_coordinate = $column_letter . $height;
        $block->setCellValue($bottom_corner_coordinate, null);
        return $block;
    }

    /**
     * Get the width of this XlsxBlock
     *
     * @return int
     */
    public function getWidth() : int
    {
        $highest_column = $this->internal_worksheet->getHighestColumn();
        return Coordinate::columnIndexFromString($highest_column);
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
     * Get the value stored in a cell in this block
     *
     * @param  string $coordinate The cell's coordinate relative to this block
     *
     * @return string
     */
    public function getCellValue($coordinate)
    {
        return $this->getCell($coordinate)->getValue();
    }

    /**
     * Get a cell from this block
     *
     * @param  string $coordinate The cell's coordinate relative to this block
     *
     * @return PhpOffice\PhpSpreadsheet\Cell\Cell
     *
     * @throws OfficeBlockParty\Exceptions\CellOutOfBlockException Thrown when the requested coordinate
     *                                                             doesn't currently exist in the cell
     */
    public function getCell($coordinate) : Cell
    {
        list($column, $row) = Coordinate::coordinateFromString($coordinate);
        $column = Coordinate::columnIndexFromString($column);

        if ($row > $this->getHeight() || $column > $this->getWidth()) {
            throw new CellOutOfBlockException(sprintf("Cell '%s' is out of range the block", $coordinate));
        }
        return $this->internal_worksheet->getCell($coordinate, true);
    }

    /**
     * Get the current cell coordinates for all data in this block relative to this block
     *
     * @return array
     */
    public function getRelativeCellCoordinates()
    {
        return $this->internal_worksheet->toArray();
    }

    /**
     * Define a subset of Worksheet methods on this object by passing them through to the internal worksheet
     *
     * @param  string $method_name
     * @param  array $parameters
     *
     * @return mixed
     */
    public function __call($method_name, $parameters)
    {
        $passthru_worksheet_methods = array_map(
            'strtolower',
            [
                'setCellValue',
                'setCellValueExplicit',
                'getCell',
            ]
        );

        if (in_array(strtolower($method_name), $passthru_worksheet_methods)) {
            return $this->internal_worksheet->$method_name(...$parameters);
        }

        throw new \BadMethodCallException("Call to undefined method " . static::class . "::$method_name() in php shell code:1");
    }
}
