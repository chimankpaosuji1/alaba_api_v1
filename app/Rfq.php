<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    //
    protected $fillable = [
        'username',
        'user_id',
        'email',
        'product_keyword',
        'product_category',
        'product_quantity',
        'product_unit',
        'description',
        'rfq_image'
    ];

    public function categories()
    {
        return $this->hasMany(Category::class,'id','product_category');
    }

}
