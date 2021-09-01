<?php

namespace SmartX\Controllers;

trait BaseReturnTrait
{
    public function message($data = [], $sessionKey = '') {
        if (config('smartx.res_log_switch')) {
            \Log::info('--------成功----------');
            \Log::info(array(
                "code" => 200,
                "message" => '',
                'data' => $data,
                'sessionKey' => $sessionKey,
            ));
        }
        $result = array(
            "code" => 200,
            "message" => '',
            'data' => $data,
            'sessionKey' => $sessionKey,
        );
        return response()->json($result);
    }

    public function errorMessage($code, $message = '',$data = null) {
        if (config('smartx.res_log_switch')) {
            \Log::info('--------失败----------');
            \Log::info(array(
                "code" => $code,
                "message" => $message,
                'data' => $data,
            ));
        }
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return response()->json($result);
    }
}
