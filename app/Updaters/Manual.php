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
}
