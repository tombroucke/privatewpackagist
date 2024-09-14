<?php

namespace App\Updaters\Contracts;

use App\Models\Release;
use Illuminate\Support\Collection;

interface Updater
{
    public function validationErrors(): Collection;

    public function fetchTitle(): string;

    public function update(): ?Release;

    public function testDownload(): bool;

    public function version(): ?string;

    public function downloadLink(): ?string;

    public function changelog(): ?string;
}
