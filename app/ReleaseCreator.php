<?php

namespace App;

use App\Exceptions\DownloadLinkNotSetException;
use App\Exceptions\VersionNotSetException;
use App\Models\Package;
use App\Models\Release;
use App\Updaters\Contracts\Updater;

class ReleaseCreator
{
    public function __construct(private Updater $updater, private Package $package) {}

    public function release($downloadPath): ?Release
    {
        $version = $this->updater->version();
        $changelog = $this->updater->changelog();
        $downloadLink = $this->updater->downloadLink();

        if ($version === null || $version === '') {
            throw new VersionNotSetException;
        }

        if ($downloadLink === null || $downloadLink === '') {
            throw new DownloadLinkNotSetException;
        }

        $existingRelease = $this->package->releases()->where('version', $version)->first();
        if ($existingRelease) {
            return $existingRelease;
        }

        // // In case we are testing, $this->package is not saved to the database
        // if (! $this->package->exists) {
        //     $release = new Release;
        //     $release->fill([
        //         'version' => $version,
        //         'changelog' => $changelog,
        //         'path' => $filePath,
        //     ]);

        //     return $release;
        // }

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $downloadPath,
        ]);

    }
}
