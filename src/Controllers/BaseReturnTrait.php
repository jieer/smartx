<?php

namespace SmartX\Controllers;

trait BaseReturnTrait
{
    protected function message($data = []) {
        $result = array(
            "code" => 200,
            "message" => '',
            'data' => $data,
        );
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    protected function errorMessage($code, $message = '',$data = []) {
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
