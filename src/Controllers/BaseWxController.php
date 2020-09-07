<?php

namespace Smartwell\Controllers;

use Illuminate\Routing\Controller;
use Jieer\Models\WxApp;
use Jieer\WX\Wx;

class BaseWxController extends Controller
{
    protected $auth;
    private $app_id;
    private $wx_app;
    private $wx;

    public function __construct()
    {
        $this->auth = auth(config('jieer.auth_guard'));
    }

    public function __get($name)
    {
        $this->initParams();
        return $this->$name;

    }

    protected function initParams() {

        $this->app_id = empty(session('app_id')) ? 0:session('app_id');
        $this->wx_app = WxApp::find($this->app_id);
        $this->wx = new Wx($this->wx_app);
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
