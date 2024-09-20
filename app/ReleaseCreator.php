<?php

namespace App;

use App\Exceptions\VersionNotSetException;
use App\Models\Package;
use App\Models\Release;
use App\Recipes\Contracts\Recipe;
use App\Recipes\Exceptions\NoDownloadLinkException;

class ReleaseCreator
{
    /**
     * Create a new instance.
     */
    public function __construct(private Recipe $recipe, private PackageDownloader $packageDownloader, private Package $package) {}

    /**
     * Create a new release.
     */
    public function release(string $version): ?Release
    {
        $version = $this->recipe->version();
        $changelog = $this->recipe->changelog();
        $downloadLink = $this->recipe->downloadLink();

        if (blank($version)) {
            throw new VersionNotSetException;
        }

        if (blank($downloadLink)) {
            throw new NoDownloadLinkException($this->recipe);
        }

        $existing = $this->package->releases()->where('version', $version)->first();

        if ($existing) {
            return $existing;
        }
        $downloadPath = $this->packageDownloader
            ->store($this->package->generateReleasePath($version));

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $downloadPath,
            'shasum' => sha1_file(storage_path('app/packages/'.$downloadPath)),
        ]);
    }
}
