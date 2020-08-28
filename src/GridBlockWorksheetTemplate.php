<?php

namespace OfficeBlockParty;

trait GridBlockWorksheetTemplate
{
    /**
     * Rows of blocks to be applied to the worksheet
     *
     * @var array
     */
    protected $block_rows = array();

    /**
     * Add a block as the first block in a new row on this worksheet
     *
     * @param  Block $block
     *
     * @return self
     */
    public function addBlockAsRow(Block $block)
    {
        $this->block_rows[] = array($block);
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Add a block as a new column on the current last block row of this worksheet
     *
     * @param  Block $block
     *
     * @return self
     */
    public function appendBlockToLastRow(Block $block)
    {
        $last_key = count($this->block_rows);
        $last_key = $last_key ?: 1; // If $last_key is 0 then set it as 1 to append to 1st row
        $this->appendBlockToRow($block, $last_key);

        return $this;
    }

    /**
     * Add a block as a new column to a specific row number on this worksheet
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function appendBlockToRow(Block $block, int $row_number)
    {
        $row_index_number = $row_number -1;
        if ($row_index_number < 0) {
            throw new \InvalidArgumentException("Row ${row_number} invalid, supplied row must be greater than 0");
        } elseif ($row_index_number && !isset($this->block_rows[$row_index_number])) {
            throw new \InvalidArgumentException("Row ${row_number} doesn't exist");
        }
        $this->block_rows[$row_index_number][] = $block;
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Insert a block as a new row after an existing row
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function insertBlockAfterRow(Block $block, $row_number)
    {
        if ($row_number < 0) {
            throw new \InvalidArgumentException("Row ${row_number} invalid, supplied row must not be negative");
        } elseif ($row_number > count($this->block_rows)) {
            throw new \InvalidArgumentException("Row ${row_number} doesn't exist");
        }
        $this->block_rows = $this->insertIntoArrayAfterIndex([$block], $this->block_rows, $row_number);
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Insert a block as a new row before an existing row
     *
     * @param  Block $block
     * @param  int   $row_number
     *
     * @return self
     */
    public function insertBlockBeforeRow(Block $block, $row_number)
    {
        if ($row_number <= 0) {
            throw new \InvalidArgumentException("Row ${row_number} invalid, supplied row must be greater than 0");
        } elseif ($row_number > count($this->block_rows)) {
            throw new \InvalidArgumentException("Row ${row_number} doesn't exist");
        }
        $this->insertBlockAfterRow($block, $row_number - 1);
        return $this;
    }

    /**
     * Insert a block into a row after an existing column
     *
     * @param  Block $block
     * @param  int   $row_number
     * @param  int   $column_number
     *
     * @return self
     */
    public function insertBlockAfterColumn(Block $block, $row_number, $column_number)
    {
        $row_index_number = $row_number -1;
        if ($row_index_number < 0) {
            throw new \InvalidArgumentException("Row ${row_number} invalid, supplied row must be greater than 0");
        } elseif ($column_number < 0) {
            throw new \InvalidArgumentException("Column ${column_number} invalid, supplied column must not be negative");
        } elseif (!isset($this->block_rows[$row_index_number])) {
            throw new \InvalidArgumentException("Row ${row_number} doesn't exist");
        } elseif ($column_number > count($this->block_rows[$row_index_number])) {
            throw new \InvalidArgumentException("Column ${column_number} doesn't exist in row ${row_number}");
        }
        $this->block_rows[$row_index_number] = $this->insertIntoArrayAfterIndex($block, $this->block_rows[$row_index_number], $column_number);
        $this->populateCellsFromBlocks($this->block_rows);
        return $this;
    }

    /**
     * Insert a block into a row before an existing column
     *
     * @param  Block  $block
     * @param  int    $row_number
     * @param  int    $column_number
     *
     * @return self
     */
    public function insertBlockBeforeColumn(Block $block, $row_number, $column_number)
    {
        if ($column_number <= 0) {
            throw new \InvalidArgumentException("Column ${column_number} invalid, supplied column must be greater than 0");
        } elseif (!isset($this->block_rows[$row_number -1][$column_number - 1])) {
            throw new \InvalidArgumentException("Column ${column_number} doesn't exist in row ${row_number}");
        }
        $this->insertBlockAfterColumn($block, $row_number, $column_number -1);
        return $this;
    }

    /**
     * Populate this worksheets cells from rows of blocks
     *
     * @param  $block_rows
     *
     * @return void
     */
    abstract protected function populateCellsFromBlocks($block_rows);

    /**
     * Insert data into an array after a specific index
     *
     * @param  mixed $data
     * @param  array $array
     * @param  int   $index
     *
     * @return array
     */
    private function insertIntoArrayAfterIndex($data, array $array, $index)
    {
        return array_merge(
            array_slice($array, 0, $index),
            array($data),
            array_slice($array, $index, count($array))
        );
    }
}
