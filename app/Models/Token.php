<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Token extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['username', 'token', 'deactivated_at'];

    /**
     * Get the token activity.
     */
    public function activity(): HasMany
    {
        return $this->hasMany(TokenActivity::class);
    }

    /**
     * Determine if the token is active.
     */
    public function isActive(): bool
    {
        return is_null($this->deactivated_at);
    }

    public function deactivatedAt(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value === true) {
                    $value = null;
                } elseif ($value === false) {
                    $value = now();
                }

                return $value;
            }
        );
    }
}
