<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends \Spatie\Permission\Models\Role
{
    //
    // public function users()
    // {
    //     return $this->belongsToMany('App\User');
    // }
    protected $hidden = [
        'guard_name','created_at', 'updated_at'
    ];
}
