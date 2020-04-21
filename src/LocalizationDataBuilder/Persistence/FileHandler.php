<?php

namespace LocalizationDataBuilder\Persistence;

use InvalidArgumentException;
use LocalizationDataBuilder\Business\JsonHelper;
use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;

class FileHandler
{
    /**
     * @var \LocalizationDataBuilder\Business\JsonHelperInterface
     */
    protected $jsonHelper;

    /**
     * @param \LocalizationDataBuilder\Business\JsonHelper $jsonHelper
     */
    public function __construct(JsonHelper $jsonHelper)
    {
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param array $replacementDataWithCorrelations
     * @param \LocalizationDataBuilder\Communication\PageRenderer $pageRenderer
     * @param \LocalizationDataBuilder\Config\Config $config
     *
     * @return void
     */
    public function writeOutFiles(array $replacementDataWithCorrelations, PageRenderer $pageRenderer, Config $config): void
    {
        $this->createLocaleFolder($config);

        $msgMaster = file_get_contents($config->getMsgMasterPath());
        $outputPath = $config->getOutputPath();
        $destinationPath = $config->getMsgDestinationPath();

        foreach ($replacementDataWithCorrelations as $localeFolderName => $fileContents) {
            $folderPath = $outputPath . "/" . $localeFolderName;
            $pageRenderer->renderWriteFolderInfo($folderPath);
            mkdir($folderPath,0777);

            $localeMessageContent = str_replace('{string}', $localeFolderName, $msgMaster);
            file_put_contents($folderPath . '/' . $destinationPath, $localeMessageContent);
            $pageRenderer->renderWriteFileInfo($destinationPath);

            foreach ($fileContents as $fileBaseName => $content) {
                $replacementsArray = $replacementDataWithCorrelations[$localeFolderName][$fileBaseName];
                $json = $this->jsonHelper->build_json($replacementsArray);

                $filename = $folderPath . '/' . $fileBaseName . '.json';
                $pageRenderer->renderWriteFileInfo($filename);
                file_put_contents($filename, $json);
            }
        }
    }

    /**
     * @param Config $config
     *
     * @return void
     */
    function createLocaleFolder(Config $config): void
    {
        $outputPath = $config->getOutputPath();

        if (true === is_dir($outputPath)) {
            $this->deleteDir($outputPath);
        }

        mkdir($outputPath,0777);
    }

    /**
     * @param string $dirPath
     *
     * @return void
     */
    function deleteDir(string $dirPath): void
    {
        if (false === is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory.");
        }

        if (false === $this->hasTrailingSlash($dirPath)) {
            $dirPath .= '/';
        }

        $files = glob($dirPath . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    function hasTrailingSlash(string $path): bool
    {
        return substr($path, -1) === '/';
    }
}