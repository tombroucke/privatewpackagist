<?php

namespace App;

use App\Exceptions\CouldNotDownloadPackageException;
use App\Exceptions\UnableToDownloadFileException;
use App\Updaters\Contracts\Updater;
use Illuminate\Support\Facades\Http;

class PackageDownloader
{
    public function __construct(private Updater $updater) {}

    public function store($path): string
    {
        $downloadLink = $this->updater->downloadLink();
        $zip = $this->fetchZip($downloadLink);

        $fullpath = storage_path('app/packages/'.$path);

        if (! file_exists(dirname($fullpath))) {
            mkdir(dirname($fullpath), 0755, true);
        }
        file_put_contents($fullpath, $zip);

        if (! file_exists($fullpath)) {
            throw new UnableToDownloadFileException($downloadLink);
        }

        if ($path === null) {
            throw new CouldNotDownloadPackageException($downloadLink);
        }

        return $path;
    }

    public function test(): bool
    {
        $downloadLink = $this->updater->downloadLink();
        $zip = $this->fetchZip($downloadLink);

        // Test if the download file is plain text, probably an error message
        if (ctype_print($zip)) {
            throw new \Exception($zip);
        }

        if (substr($zip, 0, 2) !== 'PK') {
            throw new \Exception('The file is not a zip file');
        }

        return $zip !== null;
    }

    public function fetchZip(string $link): string
    {
        return Http::withUserAgent($this->updater->userAgent())->get($link)->body();
    }
}
