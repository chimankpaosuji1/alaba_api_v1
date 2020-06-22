<?php

namespace App;

use App\Product;
use Illuminate\Database\Eloquent\Model;

class MessageCenter extends Model
{
    //
    protected $fillable = [
        'seller_id', 'buyer_id', 'product_id', 'content','read','reply'
    ];



    public function product(){
        return $this->belongsTo('App\Product');

    }
}
