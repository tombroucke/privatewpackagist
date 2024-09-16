<?php

namespace App\Updaters\Contracts;

use App\Models\Release;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;

interface Updater
{
    public static function name(): string;

    public static function slug(): string;

    public static function formSchema(): ?Section;

    public function validationErrors(): Collection;

    public function fetchPackageTitle(): string;

    public function update(): ?Release;

    public function testDownload(): bool;

    public function version(): ?string;

    public function downloadLink(): ?string;

    public function changelog(): ?string;

    public function userAgent(): ?string;
}
