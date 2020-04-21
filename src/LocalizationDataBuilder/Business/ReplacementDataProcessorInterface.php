<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;

interface ReplacementDataProcessorInterface
{
    /**
     * @param array $localeMaster
     *
     * @return array
     */
    public function composeReplacementDataForLocales(array $localeMaster): array;
}