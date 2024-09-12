<?php

namespace App\Updaters\Contracts;

use App\Models\Release;
use Illuminate\Support\Collection;

interface Updater
{
    const ENV_VARIABLES = [];

    public function validationErrors(): Collection;

    public function fetchTitle(): string;

    public function update(): ?Release;
}
