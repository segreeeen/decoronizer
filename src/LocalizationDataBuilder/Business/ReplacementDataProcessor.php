<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Persistence\FileHandlerInterface;
use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

class ReplacementDataProcessor implements ReplacementDataProcessorInterface
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
     * @var \LocalizationDataBuilder\Communication\PageRenderer
     */
    protected $pageRenderer;

    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     * @param \LocalizationDataBuilder\Persistence\FileHandlerInterface $fileHandler
     * @param \LocalizationDataBuilder\Communication\PageRenderer $pageRenderer
     */
    public function __construct(
        Config $config,
        FileHandlerInterface $fileHandler,
        PageRenderer $pageRenderer
    ) {
        $this->config = $config;
        $this->fileHandler = $fileHandler;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param array $localeMaster
     *
     * @return \LocalizationDataBuilder\Shared\ReplacementDataTransfer
     */
    public function composeReplacementDataForLocales(array $localeMaster): ReplacementDataTransfer
    {
        $replacementDataTransfer = new ReplacementDataTransfer();
        $lastTargetFile = '';

        foreach ($localeMaster as $localeMasterKey => $localeMasterValue) {
            if ($this->isCorrelated($localeMasterValue)) {
                continue;
            }

            /**
             * @var string $currentTargetFile
             */
            $currentTargetFile = $localeMasterValue[LocaleConstants::FOR_FILE];

            if ($this->isNewFile($currentTargetFile, $lastTargetFile)) {
                $this->pageRenderer->renderNewFileInfo($currentTargetFile);

                $lastTargetFile = $currentTargetFile;
            }

            $replacementDataTransfer = $this->composeReplacementDataForActiveLocales(
                $localeMaster,
                $localeMasterValue,
                $replacementDataTransfer,
                $currentTargetFile
            );
        }

        $this->addCorrelations($replacementDataTransfer);

        return $replacementDataTransfer;
    }

    /**
     * @param array $localeMaster
     * @param array $localeMasterValue
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     * @param string $currentTargetFile
     *
     * @return \LocalizationDataBuilder\Shared\ReplacementDataTransfer
     */
    protected function composeReplacementDataForActiveLocales(
        array $localeMaster,
        array $localeMasterValue,
        ReplacementDataTransfer $replacementDataTransfer,
        string $currentTargetFile
    ): ReplacementDataTransfer {
        $correlation = $localeMasterValue[LocaleConstants::CORRELATION];
        $derivationPatterns = $this->composeDerivationPatterns($correlation);

        /**
         * @var array $activeLocaleCodes
         */
        $activeLocaleCodes = $this->config->getActiveLocales();

        foreach ($activeLocaleCodes as $activeLocaleCode) {

            $stringToFind = $localeMaster[$correlation][$activeLocaleCode];
            $replacement = $localeMasterValue[$activeLocaleCode];

            $replacementData = $this->processDerivationPatterns(
                $derivationPatterns,
                $stringToFind,
                $replacement
            );

            $replacementData = $this->processReplacementData(
                $replacementData,
                $replacementDataTransfer,
                $activeLocaleCode,
                $currentTargetFile
            );

            $this->pageRenderer->renderReplaceInfo(
                $stringToFind,
                $activeLocaleCode,
                count($replacementData)
            );
        }

        return $replacementDataTransfer;
    }

    /**
     * @param string $correlation
     *
     * @return array
     */
    protected function composeDerivationPatterns(string $correlation): array
    {
        $derivativeTable = $this->config->getDerivativeTable();
        $derivationPatterns = $derivativeTable[$correlation];

        return $derivationPatterns;
    }

    /**
     * @param array $derivationPatterns
     * @param string $stringToFind
     * @param string $replacement
     *
     * @return array
     */
    protected function processDerivationPatterns(
        array $derivationPatterns,
        string $stringToFind,
        string $replacement
    ): array {
        $replacementData = [];

        foreach ($derivationPatterns as $pattern) {
            $findThis = str_replace('{string}', $stringToFind, $pattern);
            $replaceWith = str_replace('{string}', $replacement, $pattern);

            $replacementData[] = array('f' => $findThis, 'r' => $replaceWith);
        }

        return $replacementData;
    }

    /**
     * @param array $replacementData
     * @param ReplacementDataTransfer $replacementDataTransfer
     * @param string $activeLocaleCode
     * @param string $currentTargetFile
     *
     * @return array
     */
    protected function processReplacementData(
        array $replacementData,
        ReplacementDataTransfer $replacementDataTransfer,
        string $activeLocaleCode,
        string $currentTargetFile
    ): array {
        if (false === $replacementDataTransfer->hasDataForFileInLocale($activeLocaleCode, $currentTargetFile)) {
            $replacementDataTransfer->setDataForFileInLocale(
                $activeLocaleCode,
                $currentTargetFile,
                $replacementData
            );

            return $replacementData;
        }

        $currentData = $replacementDataTransfer
            ->getDataForFileInLocale(
                $activeLocaleCode,
                $currentTargetFile
            );

        $replacementData = array_merge($currentData, $replacementData);

        $replacementDataTransfer->setDataForFileInLocale(
            $activeLocaleCode,
            $currentTargetFile,
            $replacementData
        );

        return $replacementData;
    }

    /**
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     *
     * @return \LocalizationDataBuilder\Shared\ReplacementDataTransfer
     */
    protected function addCorrelations(ReplacementDataTransfer $replacementDataTransfer): ReplacementDataTransfer
    {
        $localeCorrelations = $this->config->getLocaleCorrelations();

        foreach ($localeCorrelations as $localeCorrelation => $base) {
            if ($replacementDataTransfer->hasDataForLocale($localeCorrelation)) {
                continue;
            }

            $localeDataToCopy = $replacementDataTransfer->getDataForLocale($base);

            $replacementDataTransfer->setDataForLocale(
                $localeCorrelation,
                $localeDataToCopy
            );
        }

        return $replacementDataTransfer;
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    protected function isCorrelated(array $value): bool
    {
        $id = $value[LocaleConstants::ID_TEXT];
        $correlation = $value[LocaleConstants::CORRELATION];

        return $id === $correlation;
    }

    /**
     * @param string $currentTargetFile
     * @param string $lastTargetFile
     *
     * @return bool
     */
    protected function isNewFile(string $currentTargetFile, string $lastTargetFile): bool
    {
        return $currentTargetFile != $lastTargetFile;
    }
}