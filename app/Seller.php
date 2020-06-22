<?php

namespace App;
use App\Product;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Seller extends User
{
    use HasRoles;
    protected $guard_name = 'api';
    protected $fillable = [
        'country', 'city', 'address','is_basic', 'user_id','business_name','business_type','product_category','business_reg_no','trade_assurance'
    ];
    protected $hidden = [
        'wallet'
    ];
    public function products()
    {
        return $this->hasMany('App\Product');
    }



}
