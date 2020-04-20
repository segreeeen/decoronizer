<?php

class PageRenderer
{
    /**
     * @return void
     */
    public function renderHead(): void
    {
        echo(file_get_contents('header.html'));
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
     * @param string $replacing
     * @param string $forLocale
     * @param int $count
     *
     * @return void
     */
    public function renderReplaceInfo(string $replacing, string $forLocale, int $count): void
    {
        echo("Replace 
        <span><b>" . $replacing . "</b></span> 
        for 
        <span>" . $forLocale . " </span> 
        : 
        <span>" . $count . "</span> 
        <br>\n");
    }
}