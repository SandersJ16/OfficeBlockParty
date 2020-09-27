<?php

namespace OfficeBlockParty\Test;

use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

abstract class OfficeBlockPartyTestCase extends TestCase
{
    /**
     * Get temporary file location for test.
     *
     * @return string
     */
    protected function getTempLocation()
    {
        $temp_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir);
        }
        return $temp_dir;
    }

    /**
     * Assert that a BlockWorksheet produces specific values when saved on a spreadsheet.
     *
     * @param  BlockWorksheet $worksheet
     * @param  Iterable       $expected_coordinate_values
     * @param  string         $temp_file_name
     *
     * @return void
     */
    protected function assertBlockWorksheetProducesExepectedResults($worksheet, $expected_coordinate_values, $temp_file_name)
    {
        $file_location = $this->getTempLocation() . $temp_file_name;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->disconnectWorksheets();
        $spreadsheet->addSheet($worksheet);

        $xlsx_writer = new XlsxWriter($spreadsheet);
        $xlsx_writer->save($file_location);

        $xlsx_reader = new XlsxReader();
        $rendered_spreadsheet = $xlsx_reader->load($file_location);

        $rendered_worksheet = $rendered_spreadsheet->getSheet(0);
        $rendered_cells = $rendered_worksheet->getCellCollection();

        $missing_cells = array();
        $wrong_value_cells = array();
        $extra_coordinates = array();

        // Check that all coordinates match our expected values
        foreach ($expected_coordinate_values as $coordinate => $expected_value) {
            if (!$rendered_cells->has($coordinate)) {
                $missing_cells[$coordinate] = $expected_value;
            } elseif (($actual_value = $rendered_cells->get($coordinate)->getValue()) != $expected_value) {
                $wrong_value_cells[$coordinate] = ['expected' => $expected_value, 'actual' => $actual_value];
            }
        }

        // Check if there are any unexpected coordinates
        foreach (array_diff($rendered_cells->getCoordinates(), array_keys($expected_coordinate_values)) as $extra_coordinate) {
            $extra_coordinates[$extra_coordinate] = $rendered_cells->get($extra_coordinate)->getValue();
        }

        if ($missing_cells || $wrong_value_cells || $extra_coordinates) {
            $error_message = 'Rendered XLSX file did not match expected results:' . PHP_EOL;
            foreach ($missing_cells as $coordinate => $value) {
                $error_message .= '  -  ';
                $error_message .= "Missing expected value '${value}' at coordinate ${coordinate}" . PHP_EOL;
            }
            foreach ($wrong_value_cells as $coordinate => $values) {
                $error_message .= '  -  ';
                $error_message .= "Expected value '${values['expected']}' for coordinate ${coordinate} but actual value was '${values['actual']}'" . PHP_EOL;
            }
            foreach ($extra_coordinates as $coordinate => $value) {
                $error_message .= '  -  ';
                $error_message .= "Expected coordinate ${coordinate} to not be set but had value of '${value}'" . PHP_EOL;
            }
            $error_message .= "Failed XLSX file viewable at: '${file_location}'";
            $this->fail($error_message);
        }

        unlink($file_location);
        $this->assertTrue(true);
    }
}
