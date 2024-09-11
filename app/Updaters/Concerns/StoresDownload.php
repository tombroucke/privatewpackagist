<?php

namespace App\Updaters\Concerns;

use App\Models\Package;
use Illuminate\Support\Facades\Http;

trait StoresDownload
{
    public function storeDownload(Package $package, string $link, string $version): ?string
    {
        $zip = Http::get($link)->body();

        $type = str_replace('wordpress-', '', $package->type);

        $path = "{$type}/{$package->slug}/{$package->slug}-{$version}.zip";
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
