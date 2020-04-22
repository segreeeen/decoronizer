<?php

namespace LocalizationDataBuilder\Persistence;

use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

interface FileHandlerInterface
{
    /**
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     *
     * @return void
     */
    public function writeOutFiles(
        ReplacementDataTransfer $replacementDataTransfer
    ): void;
}