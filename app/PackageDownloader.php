<?php

namespace App;

use App\Exceptions\CouldNotDownloadPackageException;
use App\Exceptions\UnableToDownloadFileException;
use App\Recipes\Contracts\Recipe;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class PackageDownloader
{
    /**
     * Create a new instance.
     */
    public function __construct(private Recipe $recipe)
    {
        //
    }

    /**
     * Store the package.
     */
    public function store($path): string
    {
        $download = $this->recipe->downloadLink();
        $zip = $this->fetchZip($download);

        $fullPath = storage_path('app/packages/'.$path);

        File::ensureDirectoryExists(dirname($fullPath));

        if (! File::put($fullPath, $zip)) {
            throw new UnableToDownloadFileException($download);
        }

        if ($path === null) {
            throw new CouldNotDownloadPackageException($download);
        }

        return $path;
    }

    /**
     * Test the download.
     */
    public function test(): bool
    {
        $downloadLink = $this->recipe->downloadLink();
        $zip = $this->fetchZip($downloadLink);

        return $zip !== null;
    }

    /**
     * Validate the zip.
     */
    public function validateZip(string $zip): string
    {
        if (blank($zip)) {
            throw new Exception('The file is empty');
        }

        if (ctype_print($zip)) {
            throw new Exception($zip);
        }

        $type = finfo_buffer(finfo_open(), $zip, FILEINFO_MIME_TYPE);

        if (! in_array($type, ['application/zip', 'application/x-zip-compressed'])) {
            throw new Exception('The downloaded file is not a valid zip file');
        }

        return $zip;
    }

    /**
     * Fetch the zip.
     */
    public function fetchZip(string $link): string
    {
        $zip = Http::withUserAgent($this->recipe->userAgent())->get($link)->body();

        return $this->validateZip($zip);
    }
}
