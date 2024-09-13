<?php

namespace App\Models;

use App\Updaters\Contracts\Updater;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $pascalCaseUpdater = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $updater)));
        $updaterClass = "App\\Updaters\\{$pascalCaseUpdater}";

        return new $updaterClass($this);
    }

    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::slug($value),
        );
    }

    public function prefix(): string
    {
        return str_replace('-', '_', strtoupper($this->slug)).'_';
    }

    public function prefixedEnvironmentVariable($variable): string
    {
        return $this->prefix().$variable;
    }

    public function environmentVariables(): Collection
    {
        $updater = $this->updater();

        return collect($updater::ENV_VARIABLES)
            ->mapWithKeys(fn ($variable) => ["{$variable}" => getenv($this->prefixedEnvironmentVariable($variable))]);
    }

    public function environmentVariable(string $variable): string
    {
        return $this->environmentVariables()[$variable] ?? null;
    }

    public function getLatestRelease(): ?Release
    {
        return $this->releases()->latest()->first();
    }

    public function getLatestReleaseAttribute(): ?string
    {
        return $this->getLatestRelease()->created_at ?? null;
    }

    public function getLatestVersionAttribute(): ?string
    {
        return $this->getLatestRelease()->version ?? null;
    }

    public function vendoredName(): string
    {
        $type = str_replace('wordpress-', '', $this->type);

        return config('app.packages_vendor_name').'-'.$type.'/'.$this->slug;
    }
}
