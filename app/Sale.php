<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //

    protected $fillable = ['buyer_id','seller_id','total_weight','total_qty','total_amount','total_amount_paid','total_shipping_amount','billing_first_name','billing_last_name','billing_email',
                            'billing_phone','billing_address', 'billing_city','billing_country', 'shipping_first_name',
                            'shipping_last_name', 'shipping_email', 'shipping_phone', 'shipping_address','shipping_city',
                            'shipping_country','sale_no','status','buyer_status','seller_status'];

    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

    public function sellers()
    {
        return $this->belongsToMany('App\Seller');
    }

    public function buyers()
    {
        return $this->belongsToMany('App\Buyer');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order');
    }
}
