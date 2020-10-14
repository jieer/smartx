<?php

namespace SmartX\Models;

use Illuminate\Database\Eloquent\Model;
use SmartX\Controllers\BaseReturnTrait;
use App\Models\Sess;
use SmartX\Models\User;

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

    public static function saveUser($user, $app_id) {
        $wx_user = self::where('openid', $user['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user)) {
            $wx_user = new WxUser();
            $wx_user->openid = $user['openid'];
            $wx_user->nickname = $user['nickname'];
            $wx_user->sex = $user['sex'];
            $wx_user->headimgurl = $user['headimgurl'];
            $wx_user->unionid = empty($user['unionid']) ? '':$user['unionid'];
            $wx_user->remark = $user['remark'];
            $wx_user->app_id = $app_id;
            $wx_user->session_key = md5($user['openid']);
            $wx_user->subscribe = 1;
        } else {
            if (empty($wx_user->unionid) && !empty($user['unionid'])) {
                $wx_user->unionid = $user['unionid'];
            }
            $wx_user->nickname = $user['nickname'];
            $wx_user->headimgurl = $user['headimgurl'];
        }
        if (!empty($wx_user->unionid)) {
            $other_user = self::where('unionid', $wx_user->unionid)->where('app_id', '!=', $app_id)->where('user_id', '>', 0)->first();
            if (!empty($other_user)) {
                $wx_user->user_id = $other_user->user_id;
            }
        }
        $wx_user->save();
        $wx_user::find($wx_user->id);
        return $wx_user;
    }


    protected function wxLogin($session, $app_id, $inviter_id = 0) {
        $wx_user = self::where('openid', $session['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user)) {
            $wx_user = new WxUser();
            $wx_user->user_id = 0;
            $wx_user->openid = $session['openid'];
            $wx_user->app_id = $app_id;
            $wx_user->nickname = '';
            $wx_user->inviter_id = $inviter_id;
            $wx_user->headimgurl = '';
        }
        if (empty($wx_user->unionid) && !empty($session['unionid'])) {
            //unionid只赋值一次
            $wx_user->unionid = $session['unionid'];
        }
        $wx_user->session_key = $session['session_key'];
        $wx_user->save();

        $user = User::find($wx_user->user_id);
        if (empty($user)) {
            return $this->message([], self::setSession($wx_user));
        }
        return $this->message([
            'access_token' => auth(config('smartx.auth_guard'))->login($user),
            'ttl' => User::getTTL(),
            'refresh_ttl' => User::getRefreshTTL(),
            'user' => $user
        ], self::setSession($wx_user)
        );
    }

    protected function setSession($wxuser) {
        $wx_id = $wxuser->id;
        $session_key = $wxuser->session_key;
        $timeout = time() + 86400;
        $this->sessionKey = encrypt("{$wx_id}\t{$session_key}\t{$timeout}");
        return $this->sessionKey;
    }

    protected function wxOffLogin($off_user, $app_id) {
        $wx_user = self::where('openid', $off_user['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user)) {
            $wx_user = new WxUser();
            $wx_user->user_id = 0;
            $wx_user->openid = $off_user['openid'];
            $wx_user->app_id = $app_id;
            $wx_user->nickname = $off_user['nickname'];
            $wx_user->headimgurl = $off_user['headimgurl'];
            $wx_user->sex = $off_user['sex'];
            if (!empty($off_user['unionid'])) {
                $mini_user = self::where('unionid', $off_user['unionid'])->where('app_id', $app_id)->first();
                if (!empty($mini_user) && !empty($mini_user->user_id)) {
                    $wx_user->user_id = $mini_user->user_id;
                }
            }
            $wx_user->session_key = md5($wx_user->openid);
        }

        if (empty($wx_user->unionid) && !empty($off_user['unionid'])) {
            //unionid只赋值一次
            $wx_user->unionid = $off_user['unionid'];
        }

        $wx_user->save();

        $user = User::find($wx_user->user_id);
        if (empty($user)) {
            return $this->message(new \ArrayObject(), self::setSession($wx_user));
        }
        return $this->message([
            'access_token' => auth(config('smartx.auth_guard'))->login($user),
            'ttl' => User::getTTL(),
            'refresh_ttl' => User::getRefreshTTL(),
            'user' => $user
        ], self::setSession($wx_user));

    }

}
