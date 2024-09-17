<?php

namespace App\Recipes;

use App\Models\Package;
use App\Models\Release;
use App\Recipes\Exceptions\ManualRecipeCanNotUpdatePackages;
use Illuminate\Support\Collection;

class Manual extends Recipe
{
    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'Manual';
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        return [];
    }

    /**
     * The validation errors for the recipe.
     */
    public function validationErrors(): Collection
    {
        $errors = new Collection;

        return $errors;
    }

    /**
     * Update the package.
     */
    public function update(): ?Release
    {
        throw new ManualRecipeCanNotUpdatePackages($this->package->slug);
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
