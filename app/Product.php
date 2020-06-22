<?php

namespace App;
use App\Reviews;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //

    protected $fillable = ['product_slug','product_id','product_name',
                            'product_details','product_description',
                            'package_content','product_highlight',
                            'product_manual','youtubeid','measurement',
                            'dimension','product_warranty','warranty_type',
                            'service_center_details','madein','keyword','brand','model',
                            'moq','option','seller_id','selling_unit','selling_package_size',
                            'single_gross_weight','package_type','package_quantity','est_days','main_image'];

    public function seller()
    {
        return $this->belongsTo('App\Seller', 'seller_id','user_id');
    }

    public function discount()
    {
        return $this->hasOne('App\Discount');
    }

    public function user()
    {
        return $this->belongsTo('App\User','seller_id');
    }
    public function categories()
    {
        return $this->belongsToMany('App\Category');
    }
    public function pictures()
    {
        return $this->belongsToMany('App\Picture');
    }
    public function variants()
    {
        return $this->belongsToMany('App\Variant');
    }
    public function pricings()
    {
        return $this->belongsToMany('App\Pricing');
    }

    public function review(){
        return $this->hasMany('App\Reviews');
    }
}
