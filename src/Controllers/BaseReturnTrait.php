<?php

namespace SmartX\Controllers;

trait BaseReturnTrait
{
    public function message($data = [], $sessionKey = '') {
        $result = array(
            "code" => 200,
            "message" => '',
            'data' => $data,
            'sessionKey' => $sessionKey,
        );
        return response()->json($result);
    }

    public function errorMessage($code, $message = '',$data = null) {
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return response()->json($result);
    }
}
