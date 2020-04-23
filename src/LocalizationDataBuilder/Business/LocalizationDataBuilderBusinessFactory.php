<?php
/**
 * Created by PhpStorm.
 * User: Chef
 * Date: 2020-04-21
 * Time: 21:23
 */

namespace LocalizationDataBuilder\Business;

use LocalizationDataBuilder\Communication\PageRenderer;
use LocalizationDataBuilder\Config\Config;
use LocalizationDataBuilder\Persistence\FileHandler;
use LocalizationDataBuilder\Persistence\FileHandlerInterface;

class LocalizationDataBuilderBusinessFactory
{
    /**
     * @var \LocalizationDataBuilder\Config\Config
     */
    protected $config;

    /**
     * @var \LocalizationDataBuilder\Communication\PageRenderer
     */
    protected $pageRenderer;

    /**
     * @param \LocalizationDataBuilder\Communication\PageRenderer $pageRenderer
     */
    public function providePageRenderer(PageRenderer $pageRenderer): void
    {
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @return \LocalizationDataBuilder\Config\Config
     */
    public function getConfig(): Config
    {
        if (false === $this->config instanceof Config) {
            $this->config = Config::buildConfig();
        }

        return $this->config;
    }

    /**
     * @return \LocalizationDataBuilder\Persistence\FileHandlerInterface
     */
    public function createFileHandler(): FileHandlerInterface
    {
        return new FileHandler();
    }

    /**
     * @return \LocalizationDataBuilder\Business\JsonHelperInterface
     */
    public function createJsonHelper(): JsonHelperInterface
    {
        return new JsonHelper();
    }

    /**
     * @return \LocalizationDataBuilder\Business\LocaleMasterProcessorInterface
     */
    public function createMasterProcessor(): LocaleMasterProcessorInterface
    {
        return new LocaleMasterProcessor(
            $this->getConfig(),
            $this->createFileHandler()
        );
    }

    /**
     * @return \LocalizationDataBuilder\Business\ReplacementDataProcessorInterface
     */
    public function createReplacementDataProcessor(): ReplacementDataProcessorInterface
    {
        return new ReplacementDataProcessor(
            $this->getConfig(),
            $this->createFileHandler(),
            $this->pageRenderer
        );
    }

    /**
     * @return \LocalizationDataBuilder\Business\MessageMasterProcessorInterface
     */
    public function createMessageMasterProcessor(): MessageMasterProcessorInterface
    {
        return new MessageMasterProcessor(
            $this->getConfig(),
            $this->createFileHandler()
        );
    }

    /**
     * @return \LocalizationDataBuilder\Business\DataWriterInterface
     */
    public function createDataWriter(): DataWriterInterface
    {
        return new DataWriter(
            $this->getConfig(),
            $this->createFileHandler(),
            $this->createJsonHelper(),
            $this->pageRenderer
        );
    }
}