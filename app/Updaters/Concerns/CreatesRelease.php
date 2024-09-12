<?php

namespace App\Updaters\Concerns;

use App\Exceptions\DownloadLinkNotSetException;
use App\Exceptions\VersionNotSetException;
use App\Models\Release;
use Illuminate\Support\Facades\Http;

trait CreatesRelease
{
    public function createRelease(string $version, string $downloadLink, string $changelog): ?Release
    {

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

        $filePath = $this->storeDownload($downloadLink, $version);

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $filePath,
        ]);
    }

    public function storeDownload(string $link, string $version): ?string
    {
        $zip = Http::get($link)->body();

        $type = str_replace('wordpress-', '', $this->package->type);

        $path = "{$type}/{$this->package->slug}/{$this->package->slug}-{$version}.zip";
        $fullpath = storage_path('app/packages/'.$path);

        if (! file_exists(dirname($fullpath))) {
            mkdir(dirname($fullpath), 0755, true);
        }
        file_put_contents($fullpath, $zip);

        if (! file_exists($fullpath)) {
            return null;
        }

        return $path;
    }
}
