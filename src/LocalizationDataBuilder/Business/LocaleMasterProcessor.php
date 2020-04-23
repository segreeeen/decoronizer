<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Persistence\FileHandlerInterface;

class LocaleMasterProcessor implements LocaleMasterProcessorInterface
{
    /**
     * @var \LocalizationDataBuilder\Config\Config
     */
    protected $config;

    /**
     * @var \LocalizationDataBuilder\Persistence\FileHandler
     */
    protected $fileHandler;

    /**
     * @param \LocalizationDataBuilder\Config\Config $config
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
     * @return array
     */
    public function processLocaleMaster(): array
    {
        $localeMaster = $this->loadLocaleMasterData();
        $localeMaster = $this->mapLocaleMasterData($localeMaster);
        $localeMaster = $this->processLocaleMasterData($localeMaster);
        $localeMaster = $this->rebuildWithIdKeys($localeMaster);

        return $localeMaster;
    }

    /**
     * @return array
     */
    protected function loadLocaleMasterData(): array
    {
        return $rawDataFromCsv = $this
            ->fileHandler
            ->readFromFileAsArray(
                $this->config->getFilenameSourceCsv()
            );
    }

    /**
     * @param array $rawDataFromCsv
     *
     * @return array
     */
    protected function mapLocaleMasterData(array $rawDataFromCsv): array
    {
        return $mappedData = array_map(
            'str_getcsv',
            $rawDataFromCsv
        );
    }

    /**
     * @param array $localeMaster
     *
     * @return array
     */
    protected function processLocaleMasterData(array $localeMaster): array
    {
        array_walk($localeMaster, function(&$a) use ($localeMaster) {
            $a = array_combine($localeMaster[0], $a);
        });
        array_shift($localeMaster); # remove column header

        return $localeMaster;
    }

    /**
     * @param array $localeMaster
     *
     * @return array
     */
    protected function rebuildWithIdKeys(array $localeMaster): array
    {
        $localeMasterWithIdKeys = [];

        foreach ($localeMaster as $item) {
            $id = $item[LocaleConstants::ID_TEXT];
            $localeMasterWithIdKeys[$id] = $item;
        }

        return $localeMasterWithIdKeys;
    }
}