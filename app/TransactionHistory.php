<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    //
    protected $fillable = ['user_id','type','description','amount'];

}
