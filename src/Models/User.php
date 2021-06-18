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
use SmartX\Services\VerifyCodeService;
use Tymon\JWTAuth\Factory;

class User extends Authenticatable implements JWTSubject
{
    use BaseReturnTrait;
    public $table;
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

    public static function userInfo($id)
    {
        $user        = User::find($id, ['id', 'phone', 'group_id', 'name', 'avatar', 'score', 'vip_id', 'vip_name', 'vip_introduction', 'self_introduction']);
        if (empty($user)) {
            return null;
        }
        $user->phone = CommonService::messyPhone($user->phone);
        return $user;
    }

    protected function registerUser($data) {
        if (!CommonService::verifPhone($data['phone'])) {
            return $this->errorMessage(400, '手机号格式不正确');
        }
        $old_user = self::where('phone', $data['phone'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(400, '该手机号已被注册，请使用手机号直接登录');
        }

        $old_user = self::where('username', $data['username'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(400, '用户名不可用，已被使用');
        }
        $data['password'] = Hash::make($data['password'] );
        $user_id = self::insertGetId($data);
        $user = self::userInfo($user_id);
        if (empty($user)) {
            return $this->errorMessage(500, '注册失败');
        }
        $token = auth(config('smartx.auth_guard'))->login($user);
        if ($token) {
            return $this->message(['access_token' => $token]);
        } else {
            return $this->errorMessage(401, '登录失败。请重新登录');
        }
    }

    /*
     * 手机号登录
     */
    protected function phoneLogin($session_key, $data)
    {
        $user = self::where('phone', $data['phone'])->first();
        if (empty($user)) {
            $user_id = self::insertGetId([
                'username' => $data['phone'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['phone']),
            ]);
            $user = self::userInfo($user_id);
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
        $result = VerifyCodeService::verify($data['action'], $data['phone'], $data['verify_code']);
        if ($result === 1) {
            return $this->message((object)null);
        } elseif ($result === 2) {
            return $this->errorMessage(400, '验证码过期');
        } else {
            return $this->errorMessage(400, '验证码无效');
        }

    }

    /*
     * 获取验证码
     */
    protected function getVerifyCode($data)
    {
        $verify_code = VerifyCodeService::generate($data['action'], $data['phone'], 300, 6, 1);
//        发送验证码
        $ret = VerifyCodeService::sendCms($data['phone'], $verify_code);
        if (empty($ret)) {
            return $this->message((object)null);
        } else {
            return $this->errorMessage(500, $ret);
        }
        //虚拟验证码
//        return $this->message((object)null);
    }


    protected function completeUser($session, $app_id, $data)
    {
        if (!CommonService::verifPhone($data['phone'])) {
            return $this->errorMessage(400, '手机号格式不正确');
        }
        $old_user = self::where('phone', $data['phone'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(400, '该手机号已被注册，请使用手机号直接登录');
        }

        $old_user = self::where('username', $data['username'])->first();
        if (!empty($old_user)) {
            return $this->errorMessage(400, '用户名不可用，已被使用');
        }
        $data['password'] = Hash::make($data['password']);
        unset($data['code']);
        $user_id = self::insertGetId($data);
        $user = self::userInfo($user_id);
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
            return $this->errorMessage(401, '登录失败。请重新登录');
        }
    }

    protected function getTTL() {
        $ttl = empty(config('jwt.ttl')) ? 60:config('jwt.ttl');
        return (int)$ttl * 60;
    }

    protected function getRefreshTTL() {
        $refresh_ttl = empty(config('jwt.refresh_ttl')) ? 20160:config('jwt.refresh_ttl');
        return (int)$refresh_ttl * 60;
    }

    public static function getNameById($user_id) {
        if (empty($user_id)) {
            return '';
        }
        $user = self::find($user_id);
        if (empty($user)) {
            return '';
        }
        return $user->name;
    }

    public function isModerator()
    {
        if ($this->group_id == 1 || $this->group_id == 2) {
            return 1;
        }
        return 0;
    }

}