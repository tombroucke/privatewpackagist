<?php

namespace App\Updaters;

use App\Exceptions\ManualUpdaterCanNotUpdatePackages;
use App\Models\Package;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Manual implements Contracts\Updater
{
    const ENV_VARIABLES = [
    ];

    public function __construct(private Package $package) {}

    public function fetchTitle(): string
    {
        $name = Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();

        return strip_tags($name);
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        return $errors;
    }

    public function update(): ?Release
    {
        throw new ManualUpdaterCanNotUpdatePackages($this->package->slug);
    }

    public function userAgent(): string
    {
        return sprintf(config('app.wp_user_agent'));
    }

    public function testDownload(): bool
    {
        return true;
    }

    public function version(): ?string
    {
        return null;
    }

    public function downloadLink(): ?string
    {
        return null;
    }

    public function changelog(): ?string
    {
        return null;
    }
}
