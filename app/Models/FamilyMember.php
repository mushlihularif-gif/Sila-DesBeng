<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_card_id',
        'nik_hash',
    ];

    public function familyCard()
    {
        return $this->belongsTo(FamilyCard::class);
    }
}
