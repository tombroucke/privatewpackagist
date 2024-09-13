<?php

namespace App;

use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class PackagesJson
{
    public function __construct() {}

    public function regenerate(): array
    {
        $packages = Package::all()
            ->mapWithKeys(function ($package) {
                $releases = $package->releases
                    ->mapwithKeys(function ($release) use ($package) {
                        return [$release->version => [
                            'name' => $package->vendoredName(),
                            'version' => $release->version,
                            'type' => $package->type,
                            'require' => [
                                'composer/installers' => '^1.0 || ^2.0',
                            ],
                            'dist' => [
                                'type' => 'zip',
                                'url' => asset('repo/'.$release->path),
                                'shasum' => sha1_file(storage_path('app/packages/'.$release->path)),
                            ],
                        ]];
                    })
                    ->sortKeysDesc();

                return [$package->vendoredName() => $releases];
            })
            ->reject(function ($package) {
                return $package->isEmpty();
            })
            ->sortKeys();

        $output = [
            'packages' => $packages->toArray(),
        ];

        Cache::put('packages.json', $output);

        return $output;
    }
}
