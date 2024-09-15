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

    private ?Updater $instantiatedUpdater = null;

    public function updater(): Updater
    {
        if (! $this->instantiatedUpdater) {
            $updater = $this->updater;
            $pascalCaseUpdater = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $updater)));
            $updaterClass = "App\\Updaters\\{$pascalCaseUpdater}";

            $this->instantiatedUpdater = new $updaterClass($this);
        }

        return $this->instantiatedUpdater;
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

    public function generateReleasePath(string $version): string
    {
        $type = str_replace('wordpress-', '', $this->type);

        return "{$type}/{$this->slug}/{$this->slug}-{$version}.zip";
    }

    public function prefixedEnvironmentVariable($variable): string
    {
        return $this->prefix().$variable;
    }

    public function environmentVariables(): Collection
    {
        $updater = $this->updater;
        $pascalCaseUpdater = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $updater)));
        $updaterClass = "App\\Updaters\\{$pascalCaseUpdater}";

        return collect($updaterClass::ENV_VARIABLES)
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
        $type = str_replace('muplugin', 'plugin', $type);

        return config('app.packages_vendor_name').'-'.$type.'/'.$this->slug;
    }

    public function validationErrors()
    {
        $errors = collect();

        try {
            $updater = $this->updater();
        } catch (\Exception $e) {
            $errors->push($e->getMessage());

            return $errors;
        }

        $this
            ->environmentVariables()
            ->each(function ($value, $key) use ($errors) {
                if (empty($value)) {
                    $errors->push("Env. variable {$this->prefixedEnvironmentVariable($key)} is required");
                }
            });

        if ($errors->isNotEmpty()) {

            return $errors;
        }

        $validationErrors = $updater->validationErrors();
        if ($validationErrors->isNotEmpty()) {
            return $validationErrors;
        }

        try {
            if (! $this->updater()->testDownload()) {
                $errors->push("Failed to download package for {$this->slug}");
            }
        } catch (\Exception $e) {
            $errors->push("Failed to download package for {$this->slug}: {$e->getMessage()}");
        }

        return $errors;
    }
}
