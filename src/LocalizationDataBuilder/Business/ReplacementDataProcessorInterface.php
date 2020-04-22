<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

interface ReplacementDataProcessorInterface
{
    /**
     * @param array $localeMaster
     *
     * @return \LocalizationDataBuilder\Shared\ReplacementDataTransfer
     */
    public function composeReplacementDataForLocales(array $localeMaster): ReplacementDataTransfer;
}