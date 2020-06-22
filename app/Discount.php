<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    //

    protected  $fillable = ['percent','product_id','start_date','end_date'];
    protected  $hidden = ['created_at','updated_at','id'];
}
