<?php

namespace App\Models;

use App\Recipes\Contracts\Recipe;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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

        return $this->instantiatedRecipe = new $class($this);
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
     * Retrieve the slug attribute.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::slug($value),
        );
    }

    /**
     * Retrieve the decrypted secrets.
     */
    public function secrets(): Collection
    {
        return collect($this->settings['secrets'] ?? [])
            ->mapWithKeys(fn ($secret, $key) => [
                $key => rescue(fn () => Crypt::decryptString($secret), $secret, false),
            ]);
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
        }

        if ($errors->isNotEmpty()) {
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
}
