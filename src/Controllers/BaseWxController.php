<?php
/**
 * Created by PhpStorm.
 * User: smartwell
 * Date: 2018/12/13
 * Time: 上午10:28
 */

namespace Smartwell\Controllers;

use Illuminate\Routing\Controller;
use Smartwell\Models\WxApp;

class BaseWxController extends Controller
{
    protected $auth;
    protected $app_id;
    protected $wx_app;

    public function __construct()
    {
        $this->app_id = empty(session('app_id')) ? 0:session('app_id');
        $this->wx_app = WxApp::find($this->app_id);
        $this->auth = auth('smartwell.auth_guard');
    }

    public function message($data = []) {
        $result = array(
            "code" => 0,
            "message" => '',
            'data' => $data,
        );
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function errorMessage($code, $message = '',$data = []) {
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
