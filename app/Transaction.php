<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    protected $fillable = ['user_id','username','email','reference','access_code','pay_type','amount','trans_id'];
}
