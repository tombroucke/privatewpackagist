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

    /**
     * Mutate the deactivated_at attribute.
     * If the value is true (toggle 'active' is on), set the value to null.
     * If the value is false (toggle 'active' is off), set the value to now.
     */
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
