<?php

namespace LocalizationDataBuilder\Config;

class Config
{
    /**
     * @var array $config
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return \LocalizationDataBuilder\Config\Config
     */
    public static function buildConfig(): Config
    {
        include('config_default.php');

        /** @var array $config */
        return new Config($config);
    }

    /**
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->config[ConfigConstants::PATH_OUTPUT];
    }

    /**
     * @return string
     */
    public function getFilenameSourceCsv(): string
    {
        return $this->config[ConfigConstants::FILENAME_SOURCE_CSV];
    }

    /**
     * @return string
     */
    public function getFilenameMsgMaster(): string
    {
        return $this->config[ConfigConstants::FILENAME_MSG_MASTER];
    }

    /**
     * @return string
     */
    public function getFilenameMsgDestination(): string
    {
        return $this->config[ConfigConstants::FILENAME_MSG_DESTINATION];
    }

    /**
     * @return string[]
     */
    public function getActiveLocales(): array
    {
        return $this->config[ConfigConstants::LOCALES_ACTIVE];
    }

    /**
     * @return string[]
     */
    public function getLocaleCorrelations(): array
    {
        return $this->config[ConfigConstants::LOCALE_CORRELATIONS];
    }

    /**
     * @return string[]
     */
    public function getDerivativeTable(): array
    {
        return $this->config[ConfigConstants::DERIVATIVE_TABLE];
    }

    /**
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->config[ConfigConstants::VERBOSE_MODE];
    }

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->config[ConfigConstants::DRY_RUN];
    }
}