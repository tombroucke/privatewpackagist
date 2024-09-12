<?php

namespace App;

use App\Models\Package;
use Illuminate\Support\Facades\Cache;

class PackagesJson
{
    public function __construct(private string $packageVendorName) {}

    private function fullPackageName(Package $package): string
    {
        $type = str_replace('wordpress-', '', $package->type);

        return $this->packageVendorName.'-'.$type.'/'.$package->slug;
    }

    public function regenerate(): array
    {
        $packages = Package::all()
            ->mapWithKeys(function ($package) {
                $fullPackageName = $this->fullPackageName($package);
                $releases = $package->releases
                    ->mapwithKeys(function ($release) use ($package, $fullPackageName) {
                        return [$release->version => [
                            'name' => $fullPackageName,
                            'version' => $release->version,
                            'type' => $package->type,
                            'require' => [
                                'composer/installers' => '^1.0',
                            ],
                            'dist' => [
                                'type' => 'zip',
                                'url' => asset('repo/'.$release->path),
                                'shasum' => sha1_file(storage_path('app/packages/'.$release->path)),
                            ],
                        ]];
                    })
                    ->sortKeysDesc();

                return [$fullPackageName => $releases];
            })
            ->reject(function ($package) {
                return $package->isEmpty();
            })
            ->sortKeys();

        $output = [
            'packages' => $packages->toArray(),
        ];

        Cache::store('database')->put('packages.json', $output);

        return $output;
    }
}
