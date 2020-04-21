<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;

interface MasterProcessorInterface
{
    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     *
     * @return array
     */
    public function processLocaleMaster(Config $config): array;
}