<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;

class MasterProcessor implements MasterProcessorInterface
{
    /**
     * @var \LocalizationDataBuilder\Config\Config
     */
    protected $config;

    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function processLocaleMaster(): array
    {
        $localeMaster = $this->loadLocaleMasterData();
        $localeMaster = $this->processLocaleMasterData($localeMaster);
        $localeMaster = $this->rebuildWithIdKeys($localeMaster);

        return $localeMaster;
    }

    /**
     * @return array
     */
    protected function loadLocaleMasterData(): array
    {
        $localeMaster = array_map(
            'str_getcsv',
            file($this->config->getSourceCsvPath())
        );

        return $localeMaster;
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
        $localeMasterIdKeys = [];

        foreach ($localeMaster as $item) {
            $id = $item[LocaleConstants::ID_TEXT];
            $localeMasterIdKeys[$id] = $item;
        }

        return $localeMasterIdKeys;
    }
}