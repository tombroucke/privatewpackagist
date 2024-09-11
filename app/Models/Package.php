<?php

namespace App\Models;

use App\Updaters\Contracts\Updater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type', 'updater', 'settings'];

    protected $casts = [
        'settings' => 'json',
    ];

    public function updater(): Updater
    {
        $updater = $this->updater;
        $pascalCaseUpdater = str_replace(' ', '', ucwords(str_replace('-', ' ', $updater)));
        $updaterClass = "App\\Updaters\\{$pascalCaseUpdater}";

        return new $updaterClass($this);
    }

    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    public function prefixedVariable($variable): string
    {
        return str_replace('-', '_', strtoupper($this->slug)).'_'.$variable;
    }

    public function environmentVariables(): Collection
    {
        $updater = $this->updater();

        return collect($updater::ENV_VARIABLES)
            ->mapWithKeys(fn ($variable) => ["{$variable}" => getenv($this->prefixedVariable($variable))]);
    }

    public function environmentVariable(string $variable): string
    {
        return $this->environmentVariables()[$variable] ?? null;
    }
}
