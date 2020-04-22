<?php

namespace LocalizationDataBuilder\Shared;

class ReplacementDataTransfer
{
    /**
     * @var array
     */
    protected $replacementData;

    /**
     * @param string $localeCode
     *
     * @return bool
     */
    public function hasDataForLocale(string $localeCode): bool
    {
        return isset($this->replacementData[$localeCode]);
    }

    /**
     * @param string $localeCode
     * @param string $filename
     *
     * @return bool
     */
    public function hasDataForFileInLocale(string $localeCode, string $filename): bool
    {
        return isset($this->replacementData[$localeCode][$filename]);
    }

    /**
     * @param string $localeCode
     *
     * @return array
     */
    public function getDataForLocale(string $localeCode): array
    {
        return $this->replacementData[$localeCode];
    }

    /**
     * @param string $localeCode
     * @param string $filename
     *
     * @return array
     */
    public function getDataForFileInLocale(string $localeCode, string $filename): array
    {
        return $this->replacementData[$localeCode][$filename];
    }

    /**
     * @return array
     */
    public function getReplacementData(): array
    {
        return $this->replacementData;
    }

    /**
     * @param string $localeCode
     * @param array $data
     *
     * @return void
     */
    public function setDataForLocale(string $localeCode, array $data): void
    {
        $this->replacementData[$localeCode] = $data;
    }

    /**
     * @param string $localeCode
     * @param string $filename
     * @param array $data
     *
     * @return void
     */
    public function setDataForFileInLocale(string $localeCode, string $filename, array $data): void
    {
        $this->replacementData[$localeCode][$filename] = $data;
    }
}