<?php

namespace SmartX\Controllers;

trait BaseReturnTrait
{
    public function message($data = [], $sessionKey = '') {
        if (empty($data)) {
            $data = (object)null;
        }
        $result = array(
            "code" => 200,
            "message" => '',
            'data' => $data,
            'sessionKey' => $sessionKey,
        );
        return response()->json($result);
    }

    public function errorMessage($code, $message = '',$data = []) {
        if (empty($data)) {
            $data = (object)null;
        }
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return response()->json($result);
    }
}
