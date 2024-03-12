<?php

namespace ModelflowAi\Tools\WebScrapping;

class WebScrappingTool
{
    public function getWebPageText(string $url): string
    {
        $html = \file_get_contents($url);
        if ($html === false) {
            throw new \Exception('Unable to retrieve web page content');
        }

        $text = (string) preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $text = (string) preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
        $text = str_replace("\n", '.', $text);
        $text = str_replace("\t", '.', $text);
        $text = str_replace("\r", '.', $text);
        $text = (string) preg_replace('/( )+/', ' ', $text);

        return (string) preg_replace('/((\.)|( \.))+/', '.', $text);
    }
}
