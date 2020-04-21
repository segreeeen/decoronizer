<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;

interface ReplacementDataProcessorInterface
{
    /**
     * @param array $localeMaster
     * @param \LocalizationDataBuilder\Config\Config $config
     *
     * @return array
     */
    public function composeReplacementDataForLocales(
        array $localeMaster,
        Config $config
    ): array;
}