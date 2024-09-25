<?php

namespace App\Models;

use App\Recipes\Contracts\Recipe;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'recipe',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'json',
        'license_valid_from' => 'datetime',
        'license_valid_to' => 'datetime',
    ];

    /**
     * The instantiated recipe instance.
     */
    private ?Recipe $instantiatedRecipe = null;

    /**
     * Get the recipe instance.
     */
    public function recipe(): Recipe
    {
        if ($this->instantiatedRecipe) {
            return $this->instantiatedRecipe;
        }

        $namespace = config('packagist.recipes.namespace');

        $recipe = $this->recipe;

        $class = Str::of($recipe)
            ->replace(['-', '_'], ' ')
            ->ucwords()
            ->replace(' ', '')
            ->start('\\')
            ->start($namespace)
            ->toString();

        return $this->instantiatedRecipe = app()->make($class, ['package' => $this]);
    }

    /**
     * Get the releases for the package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    /**
     * Get the secrets for the package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function secrets()
    {
        return $this->belongsToMany(Secret::class, 'package_secret');
    }

    /**
     * Retrieve the slug attribute.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::slug($value),
        );
    }

    /**
     * Retrieve a decrypted secret.
     */
    public function getSecret($key): ?string
    {
        $secret = $this->secrets()->where('type', $key)->first();

        if (! $secret) {
            return null;
        }

        return Crypt::decryptString($secret->value);
    }

    /**
     * Retrieve the package prefix.
     */
    public function prefix(): string
    {
        return str_replace('-', '_', strtoupper($this->slug)).'_';
    }

    /**
     * Generate the release path.
     */
    public function generateReleasePath(string $version): string
    {
        $type = str_replace('wordpress-', '', $this->type);

        return "{$type}/{$this->slug}/{$this->slug}-{$version}.zip";
    }

    /**
     * Retrieve the latest release.
     */
    public function getLatestRelease(): ?Release
    {
        return $this->releases()->latest()->first();
    }

    /**
     * Retrieve the latest release attribute.
     */
    public function getLatestReleaseAttribute(): ?string
    {
        return $this->getLatestRelease() ? $this->getLatestRelease()->created_at->diffForHumans() : null;
    }

    /**
     * Retrieve the latest version attribute.
     */
    public function getLatestVersionAttribute(): ?string
    {
        return $this->getLatestRelease() ? $this->getLatestRelease()->version : null;
    }

    /**
     * Retrieve the vendored package name.
     */
    public function vendoredName(): string
    {
        $vendor = config('packagist.vendor');

        $type = Str::of($this->type)
            ->replace('wordpress-', '')
            ->replace('muplugin', 'plugin');

        return "{$vendor}-{$type}/{$this->slug}";
    }

    /**
     * Retrieve the validation errors.
     */
    public function validationErrors()
    {
        $errors = collect();

        try {
            $recipe = $this->recipe();
        } catch (Exception $e) {
            $errors->push($e->getMessage());

            return $errors;
        }

        if ($licenseKeyError = $recipe->licenseKeyError()) {
            $errors->push($licenseKeyError);

            return $errors;
        }

        try {
            if (! $this->recipe()->testDownload()) {
                $errors->push("Failed to download package for {$this->slug}");
            }
        } catch (Exception $e) {
            $errors->push("Failed to download package for {$this->slug}: {$e->getMessage()}");
        }

        return $errors;
    }

    /**
     * Create a custom attribute for the license key validity
     * Checks if the license is valid.
     */
    public function getValidAttribute()
    {
        $licenseValidFrom = $this->license_valid_from;
        $licenseValidTo = $this->license_valid_to;
        $licenseValid = false;

        if ($licenseValidFrom && $licenseValidTo) {
            $licenseValid = now()->between($licenseValidFrom, $licenseValidTo);
        } elseif ($licenseValidFrom) {
            $licenseValid = now()->gte($licenseValidFrom);
        } elseif ($licenseValidTo) {
            $licenseValid = now()->lte($licenseValidTo);
        }

        return $licenseValid;
    }
}
