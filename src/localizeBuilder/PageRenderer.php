<?php

class PageRenderer
{
    /**
     * @return void
     */
    public function renderHeader(): void
    {
        echo(file_get_contents('header.html'));
    }

    /**
     * @return void
     */
    public function renderFoot(): void
    {
        echo(file_get_contents('footer.html'));
    }

    /**
     * @param string $text
     *
     * @return void
     */
    public function renderText(string $text): void
    {
        echo($text);
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
        echo('<hr>');
        echo('Write Folder '. $destinationFolder . '<br>');
    }

    /**
     * @param string $destinationFile
     *
     * @return void
     */
    public function renderWriteFileInfo(string $destinationFile): void
    {
        echo('Write file ' . $destinationFile . '<br>');
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
        echo('Replace 
        <span><b>' . $replacing . '</b></span> 
        for 
        <span>' . $forLocale . '</span> 
        : 
        <span>' . $count . '</span> 
        <br>\n');
    }
}