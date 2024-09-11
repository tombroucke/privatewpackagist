<?php

namespace App\Updaters\Concerns;

use League\HTMLToMarkdown\HtmlConverter;

trait ExtractsChangelog
{
    public function extractLatestChangelog(string $changelog, string $pattern): string
    {
        $converter = new HtmlConverter;
        $md = $converter->convert($changelog);
        preg_match_all('/'.$pattern.'/s', $md, $matches, PREG_SET_ORDER);

        return $matches[0][0] ?? '';
    }
}
