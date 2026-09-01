<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasarReview extends Model
{
    use HasFactory;

    protected $table = 'pasar_reviews';

    protected $fillable = [
        'pasar_produk_id',
        'user_id',
        'rating',
        'comment',
        'reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function produk()
    {
        return $this->belongsTo(PasarProduk::class, 'pasar_produk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
