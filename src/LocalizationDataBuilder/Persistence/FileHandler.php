<?php

namespace LocalizationDataBuilder\Persistence;

use InvalidArgumentException;

class FileHandler implements FileHandlerInterface
{
    /**
     * @param string $path
     * @param string $fileOrFolderName
     * @param string $extension
     *
     * @return string
     */
    public function buildPath(string $path, string $fileOrFolderName, string $extension = ''): string
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
    public function createFolder(string $folderPath): void
    {
        if (true === is_dir($folderPath)) {
            $this->deleteDir($folderPath);
        }

        mkdir($folderPath,0777);
    }

    /**
     * @param string $folderPath
     *
     * @return void
     */
    public function deleteDir(string $folderPath): void
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
    public function hasTrailingSlash(string $path): bool
    {
        return substr($path, -1) === '/';
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
     * @param string $data
     *
     * @return void
     */
    public function writeOutToFile(string $pathToFile, string $data): void
    {
        file_put_contents($pathToFile, $data);
    }
}