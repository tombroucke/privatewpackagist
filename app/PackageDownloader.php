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

        return $zip !== null;
    }

    public function validateZip(string $zip)
    {
        if (empty($zip)) {
            throw new \Exception('The file is empty');
        }

        if (ctype_print($zip)) {
            throw new \Exception($zip);
        }

        $mimeType = finfo_buffer(finfo_open(), $zip, FILEINFO_MIME_TYPE);
        $zipMimeTypes = ['application/zip', 'application/x-zip-compressed'];
        if (! in_array($mimeType, $zipMimeTypes)) {
            throw new \Exception('The downloaded file is not a valid zip file');
        }
    }

    public function fetchZip(string $link): string
    {
        $zip = Http::withUserAgent($this->updater->userAgent())->get($link)->body();
        $this->validateZip($zip);

        return $zip;
    }
}
