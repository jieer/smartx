<?php

namespace SmartX\Services;

use Illuminate\Support\Facades\Cache;
use SmartX\Models\VerifyCode;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class VerifyCodeService
{

    public static function isRepetion($ip, $phone, $action) {
        $code = VerifyCode::where('ip', $ip)->where('phone', $phone)->where('usable', 1)->first();
        if (empty($code)) {
            $code = VerifyCode::where('phone', $phone)->where('action', $action)->where('usable', 1)->first();
            if (empty($code)) {
                return false;
            }
        }
        if (time() - strtotime($code->created_at) < 60) {
            return true;
        }
        return false;
    }

    public static function generate($action, $identify, $second = 300, $length = 4, $strategy = 0)
    {
        $min  = 10 ** ($length - 1);
        $max  = (10 ** $length) - 1;
        $code = rand($min, $max);
        if ($strategy) {
            VerifyCode::where('phone', $identify)->where('action', $action)->update(['usable' => 0]);
        }
        VerifyCode::insert([
            "phone" => $identify,
            "code" => $code,
            "action" => $action,
            "ttl" => $second,
            "strategy" => $strategy,
        ]);
        return $code;
    }

    public static function verify($action, $identify, $code)
    {
        $verify = VerifyCode::where('phone', $identify)->where('action', $action)->where('code', $code)->where('usable', 1)->first();
        if (empty($verify)) {
            return 0;
        }
        if (time() - strtotime($verify->created_at) > $verify->ttl) {
            return 2;
        }
        VerifyCode::where('phone', $identify)->where('action', $action)->where('code', $code)->update(['usable' => 0]);
        return 1;
    }

    public static function sendCms($phone, $code)
    {
        $easySms = new EasySms(config('smartx.phone_verify_code_send_config'));

        try {
            $easySms->send($phone, [
                'content'  => "验证码：$code ，您正在登录，若非本人操作，请勿泄露。",
                'template' => config('smartx.phone_verify_code_send_template'),
                'data' => [
                    'code' => $code
                ],
            ]);
        } catch (NoGatewayAvailableException $e) {
            $errors = $e->getResults();
            if (count($errors) > 0) {
                foreach ($errors as $k => $error) {
                    \Log::channel('sms')->info([
                        $k => $error['exception']->raw
                    ]);
                }
            }
            return '系统错误，发送失败';
        }
        return 0;

    }
}
