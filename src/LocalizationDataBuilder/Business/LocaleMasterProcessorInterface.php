<?php

namespace LocalizationDataBuilder\Business;

interface LocaleMasterProcessorInterface
{
    /**
     * @return array
     */
    public function processLocaleMaster(): array;
}