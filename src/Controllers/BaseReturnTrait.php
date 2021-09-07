<?php

namespace SmartX\Controllers;

use Illuminate\Contracts\Support\Arrayable;
use Mockery\Exception;

trait BaseReturnTrait
{
    public function message($data = [], $sessionKey = '') {
        $result = array(
            "code" => 200,
            "message" => '',
            'data' => $data,
            'sessionKey' => $sessionKey,
        );
        return $this->output($result);
    }

    public function errorMessage($code, $message = '',$data = null) {
        $result = array(
            "code" => $code,
            "message" => $message,
            'data' => $data,
        );
        return $this->output($result);
    }
    public function output($data) {

        try {
            if (config('smartx.res_log_switch')) {
                \Log::channel('res_log')->info(request()->all());
                \Log::channel('res_log')->info(request()->getClientIp());
                \Log::channel('res_log')->info(request()->headers);
                if ($data instanceof Arrayable) {
                    \Log::channel('res_log')->info($data->toArray());
                } else {
                    \Log::channel('res_log')->info($data);
                }
            }
        } catch (\Exception $e) {
        }
        return response()->json($data);
    }
}
