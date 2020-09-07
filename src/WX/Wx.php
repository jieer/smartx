<?php

namespace SmartX\WX;

use SmartX\Models\WxApp;
use EasyWeChat\Factory;
use SmartX\Models\WxUser;

class Wx
{
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

    /*
     * 维信绑定用户(用户已登录)
     *
     */
    public function bindUser($code, $user_id) {
        $session = $this->ew_app->auth->session($code);

        if (array_key_exists('errcode', $session)) {
            return [
                'status' => $session['errcode'],
                'msg' => $session['errmsg']
            ];
        }
        return WxUser::bindUser($session, $this->wx_app->id, $user_id);
    }

    /*
     * 微信登录(用户未登录)
     */
    public function wxLogin($code) {
        $session = $this->ew_app->auth->session($code);

        if (array_key_exists('errcode', $session)) {
            return [
                'status' => $session['errcode'],
                'msg' => $session['errmsg']
            ];
        }

        return WxUser::wxLogin($session, $this->wx_app->id);
    }

    /*
     * 微信解绑(用户已登录)
     */
    public function relieveBind($code) {
        $session = $this->ew_app->auth->session($code);

        if (array_key_exists('errcode', $session)) {
            return [
                'status' => $session['errcode'],
                'msg' => $session['errmsg']
            ];
        }
        return WxUser::relieveBindUser($session, $this->wx_app->id);
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
        $res = $this->ew_app->app_code->get($path, $optional);
        if ($res instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            return $res;
        } else {
            return '';
        }
    }

    /*
     *     获取量多的小程序码
     *     成功则返回一个EasyWeChat\Kernel\Http\StreamResponse实例，可调用save()或saveAs()保存，
     */
    public function getMiniCodeB($scene, $optional) {
        $res = $this->ew_app->app_code->getUnlimit($scene, $optional);

        if ($res instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            return $res;
        } else {
            return $res;
        }
    }


    /*
     *     获取小程序二维码
     *     成功则返回一个EasyWeChat\Kernel\Http\StreamResponse实例，可调用save()或saveAs()保存，
     */
    public function getMiniQrCode($path, $optional) {

        $res = $this->ew_app->app_code->getQrCode($path, $optional);

        if ($res instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            return $res;
        } else {
            return $res;
        }

    }


}
