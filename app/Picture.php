<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    //
    protected $fillable = ['path','picture_id'];
    public function products()
    {
        return $this->hasMany('App\Product');
    }
}
