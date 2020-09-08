<?php

namespace SmartX\Controllers;

trait BaseReturnTrait
{
    public function message($data = []) {
        $result = array(
            "code" => 200,
            "message" => '',
            'data' => $data,
        );
        return response()->json($result, JSON_UNESCAPED_UNICODE);
    }

    public function errorMessage($code, $message = '',$data = []) {
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return response()->json($result, JSON_UNESCAPED_UNICODE);
    }
}
