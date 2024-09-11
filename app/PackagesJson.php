<?php

namespace App;

use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class PackagesJson
{
    public static function regenerate(): array
    {
        $packages = Package::all()
            ->mapWithKeys(function ($package) {
                $packageVendorName = config('app.packages_vendor_name').'/'.$package->slug;
                $releases = $package->releases->mapwithKeys(function ($release) use ($package) {
                    return [$release->version => [
                        'name' => config('app.packages_vendor_name').'/'.$package->slug,
                        'version' => $release->version,
                        'url' => $release->url,
                        'type' => $package->type,
                        'require' => [
                            'composer/installers' => '^1.0',
                        ],
                        'dist' => [
                            'type' => 'zip',
                            'url' => asset('repo/'.$release->path),
                        ],
                    ]];
                });

                return [$packageVendorName => $releases];
            })
            ->reject(function ($package) {
                return $package->isEmpty();
            });

        $output = [
            'packages' => $packages->toArray(),
        ];

        Cache::store('database')->put('packages.json', $output);

        return $output;
    }
}
