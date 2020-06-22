<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'seller_id','password','password_confirmation','phone','username','email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'roles','password', 'remember_token','email_verified_at','created_at', 'updated_at','role'
    ];

    //relationships will be defined below
    public function subscriptions()
    {
        return $this->hasMany('App\Subscription', 'user_id');
    }
    public function role()
    {
        return $this->belongsTo('App\Role', 'role_id');
    }
    public function wallet()
    {
        return $this->hasOne('App\Wallet', 'user_id');
    }
    public function staff()
    {
        return $this->hasOne('App\Staff', 'user_id');
    }
    public function buyer()
    {
        return $this->hasOne('App\Buyer', 'user_id');
    }

    public function seller()
    {
        return $this->hasOne('App\Seller','user_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
