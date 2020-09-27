<?php

namespace OfficeBlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class UniformGridBlockWorksheet extends BlockWorksheet implements GridBlockWorksheet
{
    use GridBlockWorksheetTemplate;

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

        $max_cell_height = 0;
        $max_cell_width = 0;
        foreach ($block_rows as $rows) {
            foreach ($rows as $block) {
                $max_cell_height = max($max_cell_height, $block->getHeight());
                $max_cell_width = max($max_cell_width, $block->getWidth());
            }
        }

        $vertical_translation = 0;
        foreach ($block_rows as $rows) {
            $horizontal_translation = 0;
            foreach ($rows as $block) {
                $relative_coordinates = $block->getRelativeCoordinates();
                foreach ($relative_coordinates as $coordinate) {
                    $coordinate_parts = Coordinate::coordinateFromString($coordinate);
                    $column_coordinate = Coordinate::columnIndexFromString($coordinate_parts[0]) + $horizontal_translation;
                    $row_coordinate = $coordinate_parts[1] + $vertical_translation;
                    $this->setCellValueByColumnAndRow($column_coordinate, $row_coordinate, $block->getCellValue($coordinate));
                }
                $horizontal_translation += $max_cell_width;
            }
            $vertical_translation += $max_cell_height;
        }
    }
}
