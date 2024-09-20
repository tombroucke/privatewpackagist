<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'version',
        'changelog',
        'path',
        'shasum',
    ];

    /**
     * Get the package that owns the release.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Determine if the release is the latest.
     */
    public function isLatest(): bool
    {
        return $this->package->getLatestRelease()->is($this);
    }
}
