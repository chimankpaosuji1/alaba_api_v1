<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advice extends Model
{
    //
    protected $fillable = [
        'content',
        'first_name',
        'last_name',
        'email',
        'phone_number'

    ];
}
