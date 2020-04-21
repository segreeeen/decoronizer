<?php

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Config\Config;

class MasterProcessor implements MasterProcessorInterface
{
    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     *
     * @return array
     */
    public function processLocaleMaster(Config $config): array
    {
        $localeMaster = $this->loadLocaleMasterData($config);
        $localeMaster = $this->processLocaleMasterData($localeMaster);
        $localeMaster = $this->rebuildWithIdKeys($localeMaster);

        return $localeMaster;
    }

    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     *
     * @return array
     */
    protected function loadLocaleMasterData(Config $config): array
    {
        $localeMaster = array_map(
            'str_getcsv',
            file($config->getSourceCsvPath())
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