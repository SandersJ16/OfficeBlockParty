<?php

namespace BlockParty;

interface Block
{
    /**
     * Get the height of this XlsxBlock
     *
     * @return int
     */
    public function getHeight();

    /**
     * Get the width of this XlsxBlock
     *
     * @return int
     */
    public function getWidth();
}
