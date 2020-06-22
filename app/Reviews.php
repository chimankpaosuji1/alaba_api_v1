<?php

namespace App;
use App\Product;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    //
    protected $fillable = [
        'username', 'product_id', 'user_id', 'review'
    ];

    public function product(){
        return $this->belongsTo('App\Product');
    }
}
