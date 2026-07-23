<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'target_url',
        'is_active',
        'is_locked',
        'sort_order',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
    ];
}
