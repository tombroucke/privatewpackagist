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
    public function __construct(private Recipe $recipe, private Package $package) {}

    /**
     * Create a new release.
     */
    public function release($downloadPath): ?Release
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

        // if (! $this->package->exists) {
        //     $release = new Release;
        //
        //     $release->fill([
        //         'version' => $version,
        //         'changelog' => $changelog,
        //         'path' => $filePath,
        //     ]);
        //
        //     return $release;
        // }

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $downloadPath,
        ]);
    }
}
