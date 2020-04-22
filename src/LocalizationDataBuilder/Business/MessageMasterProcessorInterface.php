<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

interface MessageMasterProcessorInterface
{
    /**
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     *
     * @return array
     */
    public function processMessageMaster(
        ReplacementDataTransfer $replacementDataTransfer
    ): array;
}