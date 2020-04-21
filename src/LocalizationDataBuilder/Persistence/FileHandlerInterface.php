<?php

namespace LocalizationDataBuilder\Persistence;

interface FileHandlerInterface
{
    /**
     * @param array $replacementDataWithCorrelations
     *
     * @return void
     */
    public function writeOutFiles(
        array $replacementDataWithCorrelations
    ): void;
}