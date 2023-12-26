<?php

namespace SmartX\WX;

use SmartX\Models\WxApp;
use EasyWeChat\Factory;
use SmartX\Models\WxUser;
use SmartX\Models\Id;
use SmartX\Models\User;
use SmartX\Controllers\BaseReturnTrait;
use Illuminate\Support\Facades\Hash;
use SmartX\Services\CommonUserService;
use SmartX\Services\CommonService;

class Wx
{
    use BaseReturnTrait;
    protected $ew_app;
    protected $wx_app;

    protected $wx_appid;

    public function __construct(WxApp $wx_app)
    {
        $this->wx_appid = $wx_app->appid;
        $config = [
            'app_id' => $wx_app->appid,
            'secret' => $wx_app->secret,
            'token' => $wx_app->token,
            'aes_key' => $wx_app->aes_key,
            'response_type' => 'array',
        ];

        if ($wx_app->type == 1) {
            $this->ew_app = Factory::miniProgram($config);
        } elseif($wx_app->type == 2) {
            $this->ew_app = Factory::openPlatform($config);
        } else {
            $this->ew_app = Factory::officialAccount($config);
        }
        $this->wx_app = $wx_app;
    }

    protected function setSession($wxuser) {
        $wx_id = $wxuser->id;
        $session_key = $wxuser->session_key;
        $timeout = time() + 86400;
        $this->sessionKey = encrypt("{$wx_id}\t{$session_key}\t{$timeout}");
        return $this->sessionKey;
    }

    /*
     * 维信绑定用户(用户已登录)
     *
     */
    public function bindUser($code, $user_id) {
        $session = $this->ew_app->auth->session($code);
        if (array_key_exists('errcode', $session)) {
            return $this->errorMessage($session['errcode'], $session['errmsg']);
        }
        return WxUser::bindUser($session, $this->wx_app->id, $user_id);
    }

    /*
     * 微信手机绑定
     *
     */
    public function phoneBind($data) {
        $decrypted = $this->ew_app->encryptor->decryptData($data['session_key'], $data['iv'], $data['encryptedData']);
        $wx_user = WxUser::where('session_key', $data['session_key'])->first();
        if (empty($wx_user)) {
            return $this->errorMessage(201, "未登录，请先登录");
        }
        $phone = $decrypted['phoneNumber'];
        $countryCode = $decrypted['countryCode'];

        if (empty($phone)) {
            return $this->errorMessage(201, '获取用户出错');
        }
        $user_id = Id::getId($phone);
        if ($user_id == 0) {
            return $this->errorMessage(500, "绑定失败");
        }
        $new_user = User::find($wx_user->user_id);
        if (!empty($new_user) && $new_user->id == $user_id) {
            return $this->message([
                'access_token' => auth(config('smartx.auth_guard'))->login($new_user),
                'ttl' => User::getTTL(),
                'refresh_ttl' => User::getRefreshTTL(),
            ], WxUser::setSession($wx_user));
        }
        $user = User::where('phone', $phone)->first();

        if (empty($user)) {
            $new_user = new User;
            $new_user->id = $user_id;
            $new_user->name    = config('smartx.use_nickname') ? $wx_user->nickname:CommonService::generateUserName();
            $new_user->avatar    = str_replace('http://', 'https://', $wx_user->headimgurl);
            $new_user->created_at = date('Y-m-d H:i:s');
            $new_user->phone = $phone;
            $new_user->username = config('smartx.use_nickname') ? $wx_user->nickname:CommonService::generateUserName();
            $new_user->password = Hash::make($phone);
            $new_user->save();
            $user = User::find($user_id);
            CommonUserService::registerUser($user_id);
            if (!empty($data['inviter_id'])) {
                CommonUserService::inviterUser($user_id, $data['inviter_id']);
            }
        }
        WxUser::where('id', $wx_user->id)->update([
            'user_id' => $user->id,
            'country_code' => $countryCode
        ]);
        if (!empty($wx_user->unionid)) {
            WxUser::where('unionid', $wx_user->unionid)->where('user_id', 0)->update(['user_id' => $user->id]);
        }
        return $this->message([
            'access_token' => auth(config('smartx.auth_guard'))->login($user),
            'ttl' => User::getTTL(),
            'refresh_ttl' => User::getRefreshTTL(),
        ], WxUser::setSession($wx_user));
    }


    /*
     * 完善用户信息
     */
    public function completeUser($data) {
        $decrypted = $this->ew_app->encryptor->decryptData($data['session_key'], $data['iv'], $data['encryptedData']);
        if (empty($decrypted) || empty($decrypted['openId'])) {
            return $this->errorMessage(500, '完善用户信息失败, 连接微信出错');
        }
        $wx_user = WxUser::where('session_key', $data['session_key'])->first();
        $wx_user->nickname = $decrypted['nickName'];
        $wx_user->city = $decrypted['city'];
        $wx_user->province = $decrypted['province'];
        $wx_user->country = $decrypted['country'];
        $wx_user->headimgurl = $decrypted['avatarUrl'];
        $wx_user->unionid = $decrypted['unionId'];
        $wx_user->save();

        $user = User::where('id', $wx_user->user_id)->first(['id', 'username', 'phone', 'name', 'avatar', 'created_at']);
        if (!empty($user)) {
            if (!empty($decrypted['avatarUrl'])) {
                $user->avatar = $decrypted['avatarUrl'];
            }
            if (config('smartx.use_nickname')) {
                $user->name = $decrypted['nickName'];
                $user->username = $decrypted['nickName'];
            }
            $user->save();
        }
        $wx_user = WxUser::where('session_key', $data['session_key'])->first(['id', 'nickname', 'headimgurl']);

        return $this->message($wx_user);
    }

    /*
    * 完善用户信息
    */
    public function completeUserNew($data) {
        $wx_user = WxUser::where('session_key', $data['session_key'])->first();
        if (empty($wx_user)) {
            return $this->errorMessage(400, '该用户未注册');
        }
        $wx_user->nickname = $data['nickName'];
        $wx_user->headimgurl = $data['avatarUrl'];
        $wx_user->city = empty($data['city']) ? '':$data['city'];
        $wx_user->province = empty($data['province']) ? '':$data['province'];
        $wx_user->country = empty($data['country']) ? '':$data['country'];
        $wx_user->sex = empty($data['gender']) ? 0:$data['gender'];
        $wx_user->save();

        $user = User::where('id', $wx_user->user_id)->first(['id', 'username', 'phone', 'name', 'avatar', 'created_at']);
        if (!empty($user)) {
            $user->avatar = $data['avatarUrl'];
            if (config('smartx.use_nickname')) {
                $user->name = $data['nickName'];
                $user->username = $data['nickName'];
            }
            $user->save();
        }
        $wx_user = WxUser::where('session_key', $data['session_key'])->first(['id', 'nickname', 'headimgurl']);

        return $this->message($wx_user);
    }

    /*
     * 微信登录(用户未登录)
     */
    public function wxLogin($data) {
        $session = $this->ew_app->auth->session($data['code']);

        if (array_key_exists('errcode', $session)) {
            return $this->errorMessage($session['errcode'], $session['errmsg']);
        }
        return WxUser::wxLogin($session, $this->wx_app->id, $data['inviter_id'] ?? 0);
    }

    /*
    * 微信登录(同时创建用户)
    */
    public function wxLoginAndCreateUser($data) {
        $session = $this->ew_app->auth->session($data['code']);

        if (array_key_exists('errcode', $session)) {
            return $this->errorMessage($session['errcode'], $session['errmsg']);
        }
        return WxUser::wxLoginAndCreateUser($session, $this->wx_app->id, $data['inviter_id'] ?? 0);
    }

    public function userInfo() {
        $user = User::userInfo(auth(config('smartx.auth_guard'))->user()->id);
        $wx_users = WxUser::where('user_id', $user->id)->get(['openid', 'unionid', 'nickname', 'headimgurl', 'city', 'province', 'country']);
        if (!empty($wx_users) && count($wx_users) > 0) {
            foreach ($wx_users as $wx_user) {
                if ($wx_user->sex == 1) {
                    $wx_user->sex = '男';
                } elseif ($wx_user->sex == 2) {
                    $wx_user->sex = '女';
                } else {
                    $wx_user->sex = '未知';
                }
            }
            $user->wx_user = $wx_users;
        } else {
            $user->wx_user = [];
        }
        $user->is_moderator = $user->isModerator();
        return $this->message($user);
    }

    /*
     * 微信解绑(用户已登录)
     */
    public function relieveBind($code) {
        $session = $this->ew_app->auth->session($code);

        if (array_key_exists('errcode', $session)) {
            return $this->errorMessage($session['errcode'], $session['errmsg']);
        }
        return WxUser::relieveBindUser($session, $this->wx_app->id);
    }


    /*
     * 存储用户信息
     */
    public function updateWxUserInfo($data) {

    }

    /*
     * 获取带场景值的公众号二维码
     */
    public function getCodeUrl($type = 0, $scene_id = 'open') {
        $res = empty($type) ? $this->ew_app->qrcode->temporary($scene_id, 518400):$this->ew_app->qrcode->forever();
        return empty($res['ticket']) ? '':$this->ew_app->qrcode->url($res['ticket']);
    }

    /*
     *     获取量少的小程序码
     *     成功则返回一个EasyWeChat\Kernel\Http\StreamResponse实例，可调用save()或saveAs()保存，
     */
    public function getMiniCodeA($path, $optional = []) {
        return $this->ew_app->app_code->get($path, $optional);
    }

    /*
     *     获取量多的小程序码
     *     成功则返回一个EasyWeChat\Kernel\Http\StreamResponse实例，可调用save()或saveAs()保存，
     */
    public function getMiniCodeB($scene, $optional) {
        return $this->ew_app->app_code->getUnlimit($scene, $optional);
    }


    /*
     *     获取小程序二维码
     *     成功则返回一个EasyWeChat\Kernel\Http\StreamResponse实例，可调用save()或saveAs()保存，
     */
    public function getMiniQrCode($path, $optional) {
        return $this->ew_app->app_code->getQrCode($path, $optional);
    }


}
