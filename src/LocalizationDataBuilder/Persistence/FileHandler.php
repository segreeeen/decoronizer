<?php

namespace LocalizationDataBuilder\Persistence;

use InvalidArgumentException;
use LocalizationDataBuilder\Business\JsonHelperInterface;
use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;

class FileHandler implements FileHandlerInterface
{
    /**
     * @var \LocalizationDataBuilder\Config\Config
     */
    protected $config;

    /**
     * @var \LocalizationDataBuilder\Business\JsonHelperInterface
     */
    protected $jsonHelper;

    /**
     * @var \LocalizationDataBuilder\Communication\PageRenderer
     */
    protected $pageRenderer;

    /**
     * @param \LocalizationDataBuilder\Config\Config $config
     * @param \LocalizationDataBuilder\Business\JsonHelperInterface $jsonHelper
     * @param \LocalizationDataBuilder\Communication\PageRenderer $pageRenderer
     */
    public function __construct(
        Config $config,
        JsonHelperInterface $jsonHelper,
        PageRenderer $pageRenderer
    ) {
        $this->config = $config;
        $this->jsonHelper = $jsonHelper;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param array $replacementDataWithCorrelations
     *
     * @return void
     */
    public function writeOutFiles(
        array $replacementDataWithCorrelations
    ): void {
        $this->createLocaleFolder();

        $msgMaster = file_get_contents($this->config->getMsgMasterPath());
        $outputPath = $this->config->getOutputPath();
        $destinationPath = $this->config->getMsgDestinationPath();

        foreach ($replacementDataWithCorrelations as $localeFolderName => $fileContents) {
            $folderPath = $outputPath . "/" . $localeFolderName;
            $this->pageRenderer->renderWriteFolderInfo($folderPath);
            mkdir($folderPath,0777);

            $localeMessageContent = str_replace('{string}', $localeFolderName, $msgMaster);
            file_put_contents($folderPath . '/' . $destinationPath, $localeMessageContent);
            $this->pageRenderer->renderWriteFileInfo($destinationPath);

            foreach ($fileContents as $fileBaseName => $content) {
                $replacementsArray = $replacementDataWithCorrelations[$localeFolderName][$fileBaseName];
                $json = $this->jsonHelper->build_json($replacementsArray);

                $filename = $folderPath . '/' . $fileBaseName . '.json';
                $this->pageRenderer->renderWriteFileInfo($filename);
                file_put_contents($filename, $json);
            }
        }
    }

    /**
     * @return void
     */
    protected function createLocaleFolder(): void
    {
        $outputPath = $this->config->getOutputPath();

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
    protected function deleteDir(string $dirPath): void
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
    protected function hasTrailingSlash(string $path): bool
    {
        return substr($path, -1) === '/';
    }
}