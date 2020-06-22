<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
    protected $fillable = [
        'content',
        'first_name',
        'last_name',
        'email',
        'phone'
    ];
}
