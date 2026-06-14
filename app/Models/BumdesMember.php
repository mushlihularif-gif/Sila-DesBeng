<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BumdesMember extends Model
{
    protected $fillable = ['name', 'position', 'photo', 'order'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : 'http://isewaproject.test/Admin/img/avatars/default.png';
    }
}