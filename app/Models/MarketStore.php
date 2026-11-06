<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketStore extends Model
{
    protected $fillable = ['name','description','category','address','latitude','longitude','avatar','cover','cover_position','is_open','settings'];
    protected $casts = ['settings'=>'array','is_open'=>'boolean','reviewed_at'=>'datetime','latitude'=>'float','longitude'=>'float'];
    public function owner() { return $this->belongsTo(User::class, 'user_id'); }
    public function region() { return $this->belongsTo(Region::class); }
    public function products() { return $this->hasMany(PasarProduk::class, 'store_id'); }
    public function orders() { return $this->hasMany(PasarOrder::class, 'store_id')->withTrashed(); }
    public function shippingRegion(): Region
    {
        $region = clone $this->region;
        $region->settings = $this->settings ?? [];
        return $region;
    }
}
