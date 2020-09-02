<?php
/**
 * Created by PhpStorm.
 * User: smartwell
 * Date: 2018/12/13
 * Time: 下午2:07
 */

namespace Smartwell\Models;

use Illuminate\Database\Eloquent\Model;

class WxUser extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('smartwell.database.wx_user_table');
    }

    public static function bindUser($session, $app_id, $user_id) {
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

        $wx_user->sex = !empty($session['sex']) ? $session['sex']:0;
        $wx_user->nickname = !empty($session['nickname']) ? $session['nickname']:'';
        $wx_user->headimgurl = !empty($session['headimgurl']) ? $session['headimgurl']:'';
        $wx_user->remark = !empty($session['remark']) ? $session['remark']:'';

        if (!empty($wx_user->user_id)) {
            if ($user_id == $wx_user->user_id) {
                return [
                    'status' => 0,
                ];
            }
            return [
                'status' => 1,
                'msg' => '此微信已经与别的用户绑定',
            ];

        }
        $wx_user->user_id = $user_id;
        $wx_user->save();
        return [
            'status' => 0,
        ];
    }

    public static function relieveBindUser($session, $app_id) {
        $wx_user = self::where('openid', $session['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user) || $wx_user->user_id == 0) {
            return [
                'status' => 0,
            ];
        }
        $wx_user->user_id = 0;
        $wx_user->save();
        return [
            'status' => 0,
        ];
    }

    public static function wxLogin($session, $app_id) {
        $wx_user = self::where('openid', $session['openid'])->where('app_id', $app_id)->first();
        if (empty($wx_user) || empty($wx_user->user_id)) {
            return [
                'status' => 1,
                'msg' => '该微信未绑定用户'
            ];
        }
        $user = User::find($wx_user->user_id);
        if (empty($user)) {
            self::where('openid', $session['openid'])->where('app_id', $app_id)->update(['user_id' => 0]);
            return [
                'status' => 1,
                'msg' => '该微信未绑定用户'
            ];
        }
        return [
            'status' => 1,
            'msg' => '该微信未绑定用户',
            'token' => auth(config('smartwell.auth_guard'))->login($user)
        ];
    }
}
