<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Persistence\FileHandler;

class Application
{
    /**
     * @return void
     */
    public function run(): void
    {
        $config = Config::buildConfig();

        $masterProcessor = new MasterProcessor();

        $pageRenderer = new PageRenderer($config);

        $replacementDataProcessor = new ReplacementDataProcessor($pageRenderer);

        $jsonHelper = new JsonHelper();

        $fileHandler = new FileHandler($jsonHelper);

        $pageRenderer->renderHeader();

        $localeMaster = $masterProcessor->processLocaleMaster($config);

        $replacementDataForLocales = $replacementDataProcessor->composeReplacementDataForLocales($localeMaster, $config);

        $pageRenderer->renderSeparatorLine();

        $fileHandler->writeOutFiles($replacementDataForLocales, $pageRenderer, $config);

        $pageRenderer->renderFoot();
    }
}