<?php

namespace LocalizationDataBuilder\Persistence;

use InvalidArgumentException;
use LocalizationDataBuilder\Business\JsonHelperInterface;
use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

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
     * @param string $pathToFile
     *
     * @return string
     */
    public function readFromFileAsString(string $pathToFile): string
    {
        return file_get_contents($pathToFile);
    }

    /**
     * @param string $pathToFile
     *
     * @return array
     */
    public function readFromFileAsArray(string $pathToFile): array
    {
        return file($pathToFile);
    }

    /**
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     * @param array $messageMaster
     *
     * @return void
     */
    public function writeOutFiles(
        ReplacementDataTransfer $replacementDataTransfer,
        array $messageMaster
    ): void {
        if (true === $this->config->isDryRun()) {
            return;
        }

        $outputPath = $this->config->getOutputPath();
        $this->createFolder($outputPath);

        $msgDestinationFilename = $this->config->getFilenameMsgDestination();
        $replacementData = $replacementDataTransfer->getReplacementData();

        foreach ($replacementData as $localeFolderName => $fileContents) {
            $localeFolderPath = $this->buildPath($outputPath, $localeFolderName);
            $this->createFolder($localeFolderPath);

            $this->pageRenderer->renderWriteFolderInfo($localeFolderPath);

            $localeMessageFilePath = $this->buildPath(
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

            // TODO: Actually, that's not your job, either...
            $json = $this->jsonHelper->build_json($replacementsArray);

            $localeFilePath = $this->buildPath($localeFolderPath, $fileNameWithoutExtension, '.json');
            $this->writeOutToFile($localeFilePath, $json);
            $this->pageRenderer->renderInfoWrittenFile($localeFilePath);
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
        $this->writeOutToFile($localeMessageFilePath, $localeMessageContent);

        $this->pageRenderer->renderInfoWrittenFile($msgDestinationFilename);
    }

    /**
     * @param string $folderPath
     *
     * @return void
     */
    protected function createFolder(string $folderPath): void
    {
        if (true === is_dir($folderPath)) {
            $this->deleteDir($folderPath);
        }

        mkdir($folderPath,0777);
    }

    /**
     * @param string $pathToFile
     * @param string $data
     *
     * @return void
     */
    protected function writeOutToFile(string $pathToFile, string $data): void
    {
        file_put_contents($pathToFile, $data);
    }

    /**
     * @param string $path
     * @param string $fileOrFolderName
     * @param string $extension
     *
     * @return string
     */
    protected function buildPath(string $path, string $fileOrFolderName, string $extension = ''): string
    {
        if ('' === $extension) {
            return sprintf("%s/%s", $path, $fileOrFolderName);
        }

        return sprintf("%s/%s.%s", $path, $fileOrFolderName, $extension);
    }

    /**
     * @param string $folderPath
     *
     * @return void
     */
    protected function deleteDir(string $folderPath): void
    {
        if (false === is_dir($folderPath)) {
            throw new InvalidArgumentException("$folderPath must be a directory.");
        }

        if (false === $this->hasTrailingSlash($folderPath)) {
            $folderPath .= '/';
        }

        $files = glob($folderPath . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($folderPath);
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