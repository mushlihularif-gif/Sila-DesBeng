<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_name', 'region_type', 'region_name', 'parent_region_id', 
        'reason', 'contact_email', 'contact_phone', 'status', 'user_id', 'document_path'
    ];

    public function parentRegion()
    {
        return $this->belongsTo(Region::class, 'parent_region_id');
    }
}
