<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

interface DataWriterInterface
{
    /**
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     * @param array $messageMaster
     *
     * @return void
     */
    public function writeOutData(
        ReplacementDataTransfer $replacementDataTransfer,
        array $messageMaster
    ): void;
}