<?php

namespace BlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CompactBlockWorksheet extends BlockWorksheet
{
    protected $blocks = array();

    public function addBlockAsRow(Block $block) {
        $this->blocks[] = array($block);
        $this->prime($this->blocks);
        return $this;
    }

    protected function prime($blocks) {
        $this->clearCells();

        $horizontal_translation = 0;
        $vertical_translation = 0;
        foreach ($blocks as $rows) {
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
