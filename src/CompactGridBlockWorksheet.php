<?php

namespace OfficeBlockParty;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class CompactGridBlockWorksheet extends BlockWorksheet implements GridBlockWorksheet
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

        $vertical_translation = 0;
        foreach ($block_rows as $rows) {
            $horizontal_translation = 0;
            $row_max_cell_height = 0;
            foreach ($rows as $block) {
                $relative_coordinates = $block->getRelativeCoordinates();
                foreach ($relative_coordinates as $coordinate) {
                    $coordinate_parts = Coordinate::coordinateFromString($coordinate);
                    $column_coordinate = Coordinate::columnIndexFromString($coordinate_parts[0]) + $horizontal_translation;
                    $row_coordinate = $coordinate_parts[1] + $vertical_translation;
                    $this->setCellValueByColumnAndRow($column_coordinate, $row_coordinate, $block->getCellValue($coordinate));
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
