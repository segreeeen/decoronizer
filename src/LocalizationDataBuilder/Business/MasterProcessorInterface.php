<?php

namespace LocalizationDataBuilder\Business;

interface MasterProcessorInterface
{
    /**
     * @return array
     */
    public function processLocaleMaster(): array;
}