<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Persistence\FileHandlerInterface;
use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

class MessageMasterProcessor implements MessageMasterProcessorInterface
{
    /**
     * @var \LocalizationDataBuilder\Config\Config
     */
    protected $config;

    /**
     * @var \LocalizationDataBuilder\Persistence\FileHandlerInterface
     */
    protected $fileHandler;

    /**
     * @param \LocalizationDataBuilder\Config\Config
     * @param \LocalizationDataBuilder\Persistence\FileHandlerInterface $fileHandler
     */
    public function __construct(
        Config $config,
        FileHandlerInterface $fileHandler
    ) {
        $this->config = $config;
        $this->fileHandler = $fileHandler;
    }

    /**
     * @param ReplacementDataTransfer $replacementDataTransfer
     *
     * @return array
     */
    public function processMessageMaster(
        ReplacementDataTransfer $replacementDataTransfer
    ): array {
        $msgMaster = $this->fileHandler->readFromFileAsString(
            $this->config->getFilenameMsgMaster()
        );

        $messageMaster = [];
        $replacementData = $replacementDataTransfer->getReplacementData();

        foreach ($replacementData as $localeFolderName => $fileContents) {
            $localeMessageContent = str_replace(
                LocaleConstants::PLACEHOLDER,
                $localeFolderName,
                $msgMaster
            );

            $messageMaster[$localeFolderName] = $localeMessageContent;
        }

        return $messageMaster;
    }
}