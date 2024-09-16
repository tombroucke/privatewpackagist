<?php

namespace App\Updaters\Abstracts;

use App\Events\PackageInformationEvent;
use App\Models\Package;
use App\Models\Release;
use App\PackageDownloader;
use App\ReleaseCreator;
use App\Updaters\Contracts\Updater as UpdaterContract;
use Illuminate\Support\Str;
use League\HTMLToMarkdown\HtmlConverter;

abstract class Updater implements UpdaterContract
{
    protected array $packageInformation;

    const ENV_VARIABLES = [
    ];

    public function __construct(protected Package $package) {}

    final public static function slug(): string
    {
        return Str::snake((new \ReflectionClass(static::class))->getShortName());
    }

    public function update(): ?Release
    {
        $downloadPath = (new PackageDownloader($this))
            ->store($this->package->generateReleasePath($this->version()));

        return (new ReleaseCreator($this, $this->package))
            ->release($downloadPath);
    }

    public function userAgent(): string
    {
        return config('app.wp_user_agent');
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

    /**
     * Fetch the package information from the source
     *
     * @return array{version: string, changelog: string, downloadLink: string}
     */
    abstract protected function fetchPackageInformation(): array;

    private function getPackageInformation(string $key): ?string
    {
        if (! isset($this->packageInformation)) {
            $this->packageInformation = $this->fetchPackageInformation();
        }

        event(new PackageInformationEvent($this->packageInformation, $this->package));

        return $this->packageInformation[$key] ?? null;
    }

    public function version(): ?string
    {
        return $this->getPackageInformation('version');
    }

    public function downloadLink(): ?string
    {
        return $this->getPackageInformation('downloadLink');
    }

    public function changelog(): ?string
    {
        return $this->getPackageInformation('changelog');
    }
}
