<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'page_url',
        'clicked_at',
        'viewport_width',
        'viewport_height',
        'x_coordinate',
        'y_coordinate',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'viewport_width'  => 'integer',
        'viewport_height' => 'integer',
        'x_coordinate' => 'float',
        'y_coordinate' => 'float',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
