<?php

namespace App\Recipes\Contracts;

use App\Models\Release;

interface Recipe
{
    public static function name(): string;

    public static function slug(): string;

    public static function forms(): array;

    public function licenseKeyError(): ?string;

    public function fetchPackageTitle(): string;

    public function update(): ?Release;

    public function testDownload(): bool;

    public function version(): ?string;

    public function downloadLink(): ?string;

    public function changelog(): ?string;

    public function userAgent(): ?string;
}
