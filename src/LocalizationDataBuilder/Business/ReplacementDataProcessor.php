<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;

class ReplacementDataProcessor implements ReplacementDataProcessorInterface
{
    /**
     * @var \LocalizationDataBuilder\Config\Config
     */
    protected $config;

    /**
     * @var \LocalizationDataBuilder\Communication\PageRenderer
     */
    protected $pageRenderer;

    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     * @param \LocalizationDataBuilder\Communication\PageRenderer $pageRenderer
     */
    public function __construct(Config $config, PageRenderer $pageRenderer)
    {
        $this->config = $config;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param array $localeMaster
     *
     * @return array
     */
    public function composeReplacementDataForLocales(array $localeMaster): array
    {
        $replacementDataForLocales = [];
        $lastTargetFile = '';

        foreach ($localeMaster as $key => $value) {

            if ($this->isCorrelated($value)) {
                continue;
            }

            /**
             * @var string $currentTargetFile
             */
            $currentTargetFile = $value[LocaleConstants::FOR_FILE];

            if ($this->isNewFile($currentTargetFile, $lastTargetFile)) {
                $this->pageRenderer->renderNewFileInfo($currentTargetFile);

                $lastTargetFile = $currentTargetFile;
            }

            /**
             * @var array $activeLocaleCodes
             */
            $activeLocaleCodes = $this->config->getActiveLocales();
            foreach ($activeLocaleCodes as $activeLocaleCode) {

                $correlation = $value[LocaleConstants::CORRELATION];
                $derivativeTable = $this->config->getDerivativeTable();
                $derivationPatterns = $derivativeTable[$correlation];

                $stringToFind = $localeMaster[$correlation][$activeLocaleCode];
                $replacement = $value[$activeLocaleCode];

                $findAndReplaceData = $this->composeReplacementData($derivationPatterns, $stringToFind, $replacement);

                if (isset($replacementDataForLocales[$activeLocaleCode][$currentTargetFile])) {
                    $replacementDataForLocales[$activeLocaleCode][$currentTargetFile] = array_merge(
                        $replacementDataForLocales[$activeLocaleCode][$currentTargetFile],
                        $findAndReplaceData
                    );
                } else {
                    $replacementDataForLocales[$activeLocaleCode][$currentTargetFile] = $findAndReplaceData;
                }

                $this->pageRenderer->renderReplaceInfo(
                    $localeMaster[$correlation][$activeLocaleCode],
                    $activeLocaleCode,
                    count($findAndReplaceData)
                );
            }
        }

        $replacementDataWithCorrelations = $this->addCorrelations($replacementDataForLocales);

        return $replacementDataWithCorrelations;
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
     * @param array $derivationPatterns
     * @param string $stringToFind
     * @param string $replacement
     *
     * @return array
     */
    protected function composeReplacementData(array $derivationPatterns, string $stringToFind, string $replacement): array
    {
        $replacementData = [];

        foreach ($derivationPatterns as $pattern) {
            $findThis = str_replace('{string}', $stringToFind, $pattern);
            $replaceWith = str_replace('{string}', $replacement, $pattern);

            $replacementData[] = array('f' => $findThis, 'r' => $replaceWith);
        }

        return $replacementData;
    }

    /**
     * @param array $replacementDataForLocales
     *
     * @return array
     */
    protected function addCorrelations(array $replacementDataForLocales): array
    {
        $localeCorrelations = $this->config->getLocaleCorrelations();
        $replacementDataWithCorrelations = [];

        foreach ($localeCorrelations as $localeCorrelation => $base) {
            $replacementDataWithCorrelations[$localeCorrelation] = $replacementDataForLocales[$base];
        }

        return $replacementDataWithCorrelations;
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