<?php

class PageRenderer
{
    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $text
     *
     * @return void
     */
    public function renderText(string $text): void
    {
        if (false === $this->config->isVerbose()) {
            return;
        }

        $this->renderText($text);
    }

    /**
     * @return void
     */
    public function renderHeader(): void
    {
        $this->renderText(file_get_contents('header.html'));
    }

    /**
     * @return void
     */
    public function renderFoot(): void
    {
        $this->renderText(file_get_contents('footer.html'));
    }

    /**
     * @return void
     */
    public function renderSeparatorLine(): void
    {
        $this->renderText("<hr>");
    }

    /**
     * @param string $currentTargetFile
     * *
     * @return void
     */
    public function renderNewFileInfo(string $currentTargetFile): void
    {
        $processingNewFileHeader = sprintf('--- %s.json ---<br>', $currentTargetFile);

        $this->renderText($processingNewFileHeader);
    }

    /**
     * @param string $destinationFolder
     *
     * @return void
     */
    public function renderWriteFolderInfo(string $destinationFolder): void
    {
        $this->renderText('<hr>');
        $this->renderText('Write Folder '. $destinationFolder . '<br>');
    }

    /**
     * @param string $destinationFile
     *
     * @return void
     */
    public function renderWriteFileInfo(string $destinationFile): void
    {
        $this->renderText('Write file ' . $destinationFile . '<br>');
    }

    /**
     * @param string $replacing
     * @param string $forLocale
     * @param int $count
     *
     * @return void
     */
    public function renderReplaceInfo(string $replacing, string $forLocale, int $count): void
    {
        $this->renderText('Replace 
        <span><b>' . $replacing . '</b></span> 
        for 
        <span>' . $forLocale . '</span> 
        : 
        <span>' . $count . '</span> 
        <br>\n');
    }
}