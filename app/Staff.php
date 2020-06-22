<?php

namespace App;

use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Staff extends Model
{
    protected $table = 'staffs';
    protected $fillable = ['user_id', 'job_title', 'employed_date', 'is_locked', 'balance'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];


    protected $appends = [
        'username', 'email', 'phone', 'role'
    ];

    private $auth_details = false;

    //relationships
    public function user()
    {
        return $this->belongsTo('App\User');
    }


    public function userObject()
    {
        return User::where('id', $this->user_id)->first();
    }

    private function getAuth()
    {
        if (!$this->auth_details) {
            $this->auth_details = User::where('id', $this->user_id)->first()->toArray();
        }
        return $this->auth_details;
    }



    public function getEmailAttribute()
    {
        return $this->getAuth()['email'];
    }

    public function getNameAttribute()
    {
        return $this->getAuth()['name'];
    }

    // public function getFirstNameAttribute()
    // {
    //     return $this->getAuth()['first_name'];
    // }

    // public function getLastNameAttribute()
    // {
    //     return $this->getAuth()['last_name'];
    // }

    // public function getMiddleNameAttribute()
    // {
    //     return $this->getAuth()['middle_name'];
    // }

    public function getPhoneAttribute()
    {
        return $this->getAuth()['phone'];
    }

    // public function getIsActiveAttribute()
    // {
    //     return $this->getAuth()['is_active'];
    // }

    // public function getIsOnlineAttribute()
    // {
    //     return $this->getAuth()['is_online'];
    // }

    public function getUsernameAttribute()
    {
        return $this->getAuth()['username'];
    }


    public function getRoleAttribute()
    {
        return $this->getAuth()['role'];
    }
}
