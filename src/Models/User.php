<?php

namespace Smartwell\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class User extends Authenticatable implements JWTSubject
{
    public $table;
    public $timestamps = false;
    protected $fillable = ['username', 'phone', 'name', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartwell.database.common_user_table');
    }

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