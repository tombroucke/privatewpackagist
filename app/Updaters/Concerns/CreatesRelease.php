<?php

namespace App\Updaters\Concerns;

use App\Exceptions\CouldNotDownloadPackageException;
use App\Exceptions\DownloadLinkNotSetException;
use App\Exceptions\UnableToDownloadFileException;
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

        if ($filePath === null) {
            throw new CouldNotDownloadPackageException($downloadLink);
        }

        return $this->package->releases()->create([
            'version' => $version,
            'changelog' => $changelog,
            'path' => $filePath,
        ]);
    }

    public function storeDownload(string $link, string $version): ?string
    {
        $zip = $this->fetchZip($link);

        $type = str_replace('wordpress-', '', $this->package->type);

        $path = "{$type}/{$this->package->slug}/{$this->package->slug}-{$version}.zip";
        $fullpath = storage_path('app/packages/'.$path);

        if (! file_exists(dirname($fullpath))) {
            mkdir(dirname($fullpath), 0755, true);
        }
        file_put_contents($fullpath, $zip);

        if (! file_exists($fullpath)) {
            throw new UnableToDownloadFileException($link);
        }

        return $path;
    }

    public function fetchZip(string $link): string
    {
        return Http::get($link)->body();
    }
}
