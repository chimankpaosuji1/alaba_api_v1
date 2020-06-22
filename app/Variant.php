<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    //
    protected $fillable = ['color','size','material', 'quantity','variant_sku','price','to_purchase'];
    public function products()
    {
        return $this->belongsToMany('App\Products');
    }
}
