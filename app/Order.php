<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = ['order_no','variant_sku','product_sku','quantity','buyer_id','seller_id'];


}
