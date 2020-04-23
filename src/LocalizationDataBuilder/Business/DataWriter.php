<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Persistence\FileHandlerInterface;
use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

class DataWriter implements DataWriterInterface
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
     * @var \LocalizationDataBuilder\Persistence\FileHandlerInterface
     */
    protected $jsonHelper;

    /**
     * @var \LocalizationDataBuilder\Communication\PageRenderer
     */
    protected $pageRenderer;

    public function __construct(
        Config $config,
        FileHandlerInterface $fileHandler,
        JsonHelperInterface $jsonHelper,
        PageRenderer $pageRenderer
    ) {
        $this->config = $config;
        $this->fileHandler = $fileHandler;
        $this->jsonHelper = $jsonHelper;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     * @param array $messageMaster
     *
     * @return void
     */
    public function writeOutData(
        ReplacementDataTransfer $replacementDataTransfer,
        array $messageMaster
    ): void {
        if (true === $this->config->isDryRun()) {
            return;
        }

        $outputPath = $this->config->getOutputPath();
        $this->fileHandler->createFolder($outputPath);

        $msgDestinationFilename = $this->config->getFilenameMsgDestination();
        $replacementData = $replacementDataTransfer->getReplacementData();

        foreach ($replacementData as $localeFolderName => $fileContents) {
            $localeFolderPath = $this->fileHandler->buildPath($outputPath, $localeFolderName);
            $this->fileHandler->createFolder($localeFolderPath);

            $this->pageRenderer->renderWriteFolderInfo($localeFolderPath);

            $localeMessageFilePath = $this->fileHandler->buildPath(
                $localeFolderPath,
                $msgDestinationFilename
            );

            $this->writeOutMessageMaster(
                $localeMessageFilePath,
                $msgDestinationFilename,
                $messageMaster[$localeFolderName]
            );

            $this->writeOutReplacementData(
                $localeFolderPath,
                $localeFolderName,
                $fileContents,
                $replacementDataTransfer
            );
        }
    }

    /**
     * @param string $localeFolderPath
     * @param string $localeFolderName
     * @param array $fileContents
     * @param ReplacementDataTransfer $replacementDataTransfer
     *
     * @return void
     */
    protected function writeOutReplacementData(
        string $localeFolderPath,
        string $localeFolderName,
        array $fileContents,
        ReplacementDataTransfer $replacementDataTransfer
    ): void {
        foreach ($fileContents as $fileNameWithoutExtension => $content) {
            $replacementsArray = $replacementDataTransfer
                ->getDataForFileInLocale(
                    $localeFolderName,
                    $fileNameWithoutExtension
                );

            $json = $this
                ->jsonHelper
                ->build_json($replacementsArray);

            $localeFilePath = $this
                ->fileHandler
                ->buildPath(
                    $localeFolderPath,
                    $fileNameWithoutExtension,
                    '.json'
                );
            $this
                ->fileHandler
                ->writeOutToFile($localeFilePath, $json);

            $this
                ->pageRenderer
                ->renderInfoWrittenFile($localeFilePath);
        }
    }

    /**
     * @param string $localeMessageFilePath
     * @param string $msgDestinationFilename
     * @param string $localeMessageContent
     *
     * @return void
     */
    protected function writeOutMessageMaster(
        string $localeMessageFilePath,
        string $msgDestinationFilename,
        string $localeMessageContent
    ): void {
        $this
            ->fileHandler
            ->writeOutToFile($localeMessageFilePath, $localeMessageContent);

        $this
            ->pageRenderer
            ->renderInfoWrittenFile($msgDestinationFilename);
    }
}