<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    //
    protected $fillable = ['min_qty','max_qty','price'];
    public function products()
    {
        return $this->hasMany('App\Products');
    }
}
