<?php

namespace LocalizationDataBuilder\Communication;

use LocalizationDataBuilder\Config\Config;

class LocalizationDataBuilderCommunicationFactory
{
    /**
     * @return \LocalizationDataBuilder\Config\Config
     */
    public function getConfig(): Config
    {
        return Config::buildConfig();
    }

    /**
     * @return \LocalizationDataBuilder\Communication\PageRenderer
     */
    public function createPageRenderer(): PageRenderer
    {
        return new PageRenderer(
            $this->getConfig()
        );
    }
}