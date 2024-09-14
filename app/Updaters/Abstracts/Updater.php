<?php

namespace App\Updaters\Abstracts;

use App\Models\Package;
use App\Models\Release;
use App\PackageDownloader;
use App\ReleaseCreator;
use League\HTMLToMarkdown\HtmlConverter;

abstract class Updater
{
    const ENV_VARIABLES = [
    ];

    protected ?string $version = null;

    protected ?string $changelog = '';

    protected ?string $downloadLink = null;

    public function __construct(protected Package $package)
    {
        [$this->version, $this->changelog, $this->downloadLink] = $this->packageInformation();
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function downloadLink(): ?string
    {
        return $this->downloadLink;
    }

    public function changelog(): ?string
    {
        return $this->changelog;
    }

    public function update(): ?Release
    {
        $downloadPath = (new PackageDownloader($this))
            ->store($this->package->generateReleasePath($this->version()));

        return (new ReleaseCreator($this, $this->package))
            ->release($downloadPath);
    }

    public function extractLatestChangelog(string $changelog, string $pattern): string
    {
        $converter = new HtmlConverter;
        $md = $converter->convert($changelog);
        preg_match_all('/'.$pattern.'/s', $md, $matches, PREG_SET_ORDER);

        return $matches[0][0] ?? '';
    }

    public function testDownload(): bool
    {
        return (new PackageDownloader($this))
            ->test();
    }

    abstract protected function packageInformation(): array;
}
