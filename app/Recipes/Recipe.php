<?php

namespace App\Recipes;

use App\Events\PackageInformationEvent;
use App\Models\Package;
use App\Models\Release;
use App\PackageDownloader;
use App\Recipes\Contracts\Recipe as RecipeContract;
use App\ReleaseCreator;
use Illuminate\Support\Str;
use League\HTMLToMarkdown\HtmlConverter;

abstract class Recipe implements RecipeContract
{
    /**
     * The package information.
     *
     * @var array<string, string>
     */
    protected array $packageInformation;

    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [];

    /**
     * Create a new Recipe instance.
     *
     * @return void
     */
    public function __construct(protected Package $package)
    {
        //
    }

    /**
     * Get the recipe slug.
     */
    public static function slug(): string
    {
        return Str::snake((new \ReflectionClass(static::class))->getShortName());
    }

    /**
     * The package title.
     */
    public function fetchPackageTitle(): string
    {
        return Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->stripTags()
            ->trim();
    }

    /**
     * Retrieve the recipe secrets.
     */
    public static function secrets(): array
    {
        return static::$secrets;
    }

    /**
     * Retrieve the recipe forms.
     */
    public static function forms(): array
    {
        return [];
    }

    /**
     * Update and create a new release for the package associated with the recipe.
     */
    public function update(): ?Release
    {
        $downloadPath = (new PackageDownloader($this))
            ->store($this->package->generateReleasePath($this->version()));

        return (new ReleaseCreator($this, $this->package))
            ->release($downloadPath);
    }

    /**
     * Retrieve the user agent used for the recipe endpoint.
     */
    public function userAgent(): string
    {
        return config('packagist.user_agent');
    }

    /**
     * Extract the latest changelog from the provided changelog.
     */
    public function extractLatestChangelog(string $changelog, string $pattern): string
    {
        $converter = new HtmlConverter;
        $md = $converter->convert($changelog);
        preg_match_all('/'.$pattern.'/s', $md, $matches, PREG_SET_ORDER);

        return $matches[0][0] ?? '';
    }

    /**
     * Test the download link for the package.
     */
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

    /**
     * Retrieve the package information.
     */
    private function getPackageInformation(string $key): ?string
    {
        if (! isset($this->packageInformation)) {
            $this->packageInformation = $this->fetchPackageInformation();
        }

        event(new PackageInformationEvent($this->packageInformation, $this->package));

        return $this->packageInformation[$key] ?? null;
    }

    /**
     * Retrieve the package version.
     */
    public function version(): ?string
    {
        return $this->getPackageInformation('version');
    }

    /**
     * Retrieve the download link for the package.
     */
    public function downloadLink(): ?string
    {
        return $this->getPackageInformation('downloadLink');
    }

    /**
     * Retrieve the changelog for the package.
     */
    public function changelog(): ?string
    {
        return $this->getPackageInformation('changelog');
    }
}
