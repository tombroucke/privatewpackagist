<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory;

    protected $fillable = ['version', 'changelog', 'path'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function isLatest()
    {
        return $this->package->getLatestRelease()->is($this);
    }
}
