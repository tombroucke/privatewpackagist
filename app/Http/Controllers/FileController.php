<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController
{
    /**
     * Download the specified file.
     */
    public function download(string $file): BinaryFileResponse
    {
        if (Str::contains($file, '..')) {
            abort(403);
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (! in_array($extension, ['zip'])) {
            abort(403);
        }

        $path = storage_path("app/packages/{$file}");

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
