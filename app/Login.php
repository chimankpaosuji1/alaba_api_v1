<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    //

    protected $fillable = ['ip','country', 'status','user_id','region'];
}
