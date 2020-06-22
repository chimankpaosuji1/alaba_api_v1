<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
class Buyer extends User
{
    use HasRoles;
    protected $guard_name = 'api';
    protected $fillable = [
        'country', 'city', 'address','user_id','is_basic'
    ];

    protected $hidden = [
        'wallet'
    ];

    public function sales()
    {
        return $this->hasMany('App\Sale','buyer_id','user_id');
    }
}
