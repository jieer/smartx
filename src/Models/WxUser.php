<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;
use SmartX\Controllers\BaseReturnTrait;

class WxUser extends Model
{
    use BaseReturnTrait;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartx.database.wx_user_table');
    }

    protected function bindUser($session, $app_id, $user_id) {
        $wx_user = self::where('openid', $session['openid'])->where('app_id', $app_id)->first();
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
        return $this->message();
    }

    protected function relieveBindUser($session, $app_id) {
        $wx_user = self::where('openid', $session['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user) || $wx_user->user_id == 0) {
            return $this->message();
        }
        $wx_user->user_id = 0;
        $wx_user->save();
        return $this->message();
    }

    protected function wxLogin($session, $app_id) {
        \Log::info($session);
        $wx_user = self::where('openid', $session['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user)) {
            $wx_user = new WxUser();
            $wx_user->user_id = 0;
            $wx_user->openid = $session['openid'];
            $wx_user->app_id = $app_id;
        }
        if (empty($wx_user->unionid) && !empty($session['unionid'])) {
            //unionid只赋值一次
            $wx_user->unionid = $session['unionid'];
        }
        $wx_user->save();

        $user = User::find($wx_user->user_id);
        if (empty($user)) {
            return $this->message([
                'session_key' => $this->getSessionKey($wx_user->id)
            ]);
        }
        return $this->message([
            'access_token' => auth(config('smartx.auth_guard'))->login($user)
        ]);
    }

}
