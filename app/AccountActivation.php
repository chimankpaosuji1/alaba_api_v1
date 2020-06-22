<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountActivation extends Model
{
    //
    protected $fillable = [
        'token','status','created_at',
    ];
}
