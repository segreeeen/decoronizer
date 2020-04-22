<?php

namespace LocalizationDataBuilder\Persistence;

use LocalizationDataBuilder\Shared\ReplacementDataTransfer;

interface FileHandlerInterface
{
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
     * @param \LocalizationDataBuilder\Shared\ReplacementDataTransfer $replacementDataTransfer
     * @param array $messageMaster
     *
     * @return void
     */
    public function writeOutFiles(
        ReplacementDataTransfer $replacementDataTransfer,
        array $messageMaster
    ): void;
}