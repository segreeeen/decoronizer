<?php

namespace LocalizationDataBuilder\Persistence;

use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

interface FileHandlerInterface
{
    /**
     * @param string $path
     * @param string $fileOrFolderName
     * @param string $extension
     *
     * @return string
     */
    public function buildPath(string $path, string $fileOrFolderName, string $extension = ''): string;

    /**
     * @param string $folderPath
     *
     * @return void
     */
    public function createFolder(string $folderPath): void;

    /**
     * @param string $folderPath
     *
     * @return void
     */
    public function deleteDir(string $folderPath): void;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function hasTrailingSlash(string $path): bool;

    /**
     * @param string $pathToFile
     *
     * @return string
     */
    public function readFromFileAsString(string $pathToFile): string;

    /**
     * @param string $pathToFile
     *
     * @return array
     */
    public function readFromFileAsArray(string $pathToFile): array;

    /**
     * @param string $pathToFile
     * @param string $data
     *
     * @return void
     */
    public function writeOutToFile(string $pathToFile, string $data): void;
}