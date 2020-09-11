<?php

namespace SmartX\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use SmartX\Services\CommonService;
use SmartX\Controllers\BaseReturnTrait;
use Illuminate\Support\Facades\Hash;
use SmartX\Models\WxUser;

class User extends Authenticatable implements JWTSubject
{
    use BaseReturnTrait;
    public $table;
    public $timestamps = false;
    protected $fillable = ['username', 'phone', 'name', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.common_user_table');
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

    protected function registerUser($data) {
        if (!CommonService::verifPhone($data['phone'])) {
            return $this->errorMessage(422, '手机号格式不正确');
        }
        $old_user = self::where('phone', $data['phone'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(422, '该手机号已被注册，请使用手机号直接登录');
        }

        $old_user = self::where('username', $data['username'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(422, '用户名不可用，已被使用');
        }
        $data['password'] = Hash::make($data['password'] );
        $user_id = self::insertGetId($data);
        $user = self::find($user_id);
        if (empty($user)) {
            return $this->errorMessage(500, '注册失败');
        }
        $token = auth(config('smartx.auth_guard'))->login($user);
        if ($token) {
            return $this->message(['access_token' => $token]);
        } else {
            return $this->errorMessage(500, '登录失败。请重新登录');
        }
    }

    /*
     * 手机号登录
     */
    protected function phoneLogin($data)
    {
        $user = self::where('phone', $data['phone'])->first();
        if (empty($user)) {
            return $this->errorMessage(500, '该用户不存在');
        }
        $token = auth(config('smartx.auth_guard'))->login($user);
        if ($token) {
            return $this->message(['access_token' => $token]);
        } else {
            return $this->errorMessage(500, '登录失败。请重新登录');
        }
    }

    /*
     * 验证验证码
     */
    protected function verifyCode($data)
    {
        if ($data['verify_code'] == 1234) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 获取验证码
     */
    protected function getVerifyCode($phone)
    {
        return $this->message([]);
    }


    protected function completeUser($session, $app_id, $data)
    {
        if (!CommonService::verifPhone($data['phone'])) {
            return $this->errorMessage(422, '手机号格式不正确');
        }
        $old_user = self::where('phone', $data['phone'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(422, '该手机号已被注册，请使用手机号直接登录');
        }

        $old_user = self::where('username', $data['username'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(422, '用户名不可用，已被使用');
        }
        $data['password'] = Hash::make($data['password']);
        unset($data['code']);
        $user_id = self::insertGetId($data);
        $user = self::find($user_id);
        if (empty($user)) {
            return $this->errorMessage(500, '保存失败');
        }
        $wx_user = WxUser::where('openid', $session['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user)) {
            $wx_user = new WxUser();
            $wx_user->user_id = $user_id;
            $wx_user->openid = $session['openid'];
            $wx_user->app_id = $app_id;
        }
        if (empty($wx_user->unionid) && !empty($session['unionid'])) {
            //unionid只赋值一次
            $wx_user->unionid = $session['unionid'];
        }

        $wx_user->user_id = $user_id;
        $wx_user->save();

        $token = auth(config('smartx.auth_guard'))->login($user);
        if ($token) {
            return $this->message(['access_token' => $token]);
        } else {
            return $this->errorMessage(500, '登录失败。请重新登录');
        }
    }


}