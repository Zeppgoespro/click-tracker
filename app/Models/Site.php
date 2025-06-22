<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'site_key',
        'screenshot_path',
    ];

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
