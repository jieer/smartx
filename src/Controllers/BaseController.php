<?php

namespace SmartX\Controllers;

use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    protected $auth;

    public function __construct()
    {
        $this->auth = auth('smartx.auth_guard');
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
