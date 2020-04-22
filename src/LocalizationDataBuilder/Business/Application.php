<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Communication\LocalizationDataBuilderCommunicationFactory;

class Application
{
    /**
     * @var \LocalizationDataBuilder\Communication\LocalizationDataBuilderCommunicationFactory
     */
    protected $localizationDataBuilderCommunicationFactory;

    /**
     * @var \LocalizationDataBuilder\Business\LocalizationDataBuilderBusinessFactory
     */
    protected $localizationDataBuilderBusinessFactory;

    /**
     * @return void
     */
    public function run(): void
    {
        $this->boot();

        $pageRenderer = $this
            ->localizationDataBuilderCommunicationFactory
            ->createPageRenderer();
        $pageRenderer->renderHeader();

        $masterProcessor = $this
            ->localizationDataBuilderBusinessFactory
            ->createMasterProcessor();
        $localeMaster = $masterProcessor
            ->processLocaleMaster();

        $replacementDataProcessor = $this
            ->localizationDataBuilderBusinessFactory
            ->createReplacementDataProcessor();
        $replacementDataTransfer = $replacementDataProcessor
            ->composeReplacementDataForLocales($localeMaster);

        $pageRenderer->renderSeparatorLine();

        $messageMasterProcessor = $this
            ->localizationDataBuilderBusinessFactory
            ->createMessageMasterProcessor();
        $messageMaster = $messageMasterProcessor
            ->processMessageMaster(
                $replacementDataTransfer
            );

        $fileHandler = $this
            ->localizationDataBuilderBusinessFactory
            ->createFileHandler();
        $fileHandler->writeOutFiles(
            $replacementDataTransfer,
            $messageMaster
        );

        $pageRenderer->renderFoot();
    }

    /**
     * @return void
     */
    protected function boot(): void
    {
        $this->localizationDataBuilderBusinessFactory = new LocalizationDataBuilderBusinessFactory();
        $this->localizationDataBuilderCommunicationFactory = new LocalizationDataBuilderCommunicationFactory();

        $pageRenderer = $this
            ->localizationDataBuilderCommunicationFactory
            ->createPageRenderer();

        $this
            ->localizationDataBuilderBusinessFactory
            ->providePageRenderer($pageRenderer);
    }
}