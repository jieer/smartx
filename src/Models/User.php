<?php

namespace Smartwell\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\Backend\AuthUserTrait;



class User extends Authenticatable implements JWTSubject
{
    use AuthUserTrait;
    public $table='common_user';
    public $timestamps = false;
    protected $fillable = ['username', 'phone', 'name', 'password'];
    protected $hidden = ['password', 'remember_token'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}