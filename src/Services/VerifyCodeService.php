<?php

namespace SmartX\Services;

use Illuminate\Support\Facades\Cache;
use SmartX\Models\VerifyCode;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class VerifyCodeService
{

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
        //测试用验证码
        if ($verify->ttl == 0) {
            return 1;
        } else {
            return 2;
        }

        if (time() - strtotime($verify->created_at) > $verify->ttl) {
            return 2;
        }
        return 1;
    }

    public static function sendCms($phone, $code)
    {

        $easySms = new EasySms(config('smartx.phone_verify_code_send_config'));

        try {
            $easySms->send($phone, [
                'content'  => "您的验证码为: $code",
                'template' => 'SMS_208715038',
                'data' => [
                    'code' => $code
                ],
            ]);
        } catch (NoGatewayAvailableException $e) {
            \Log::channel('sms')->info($e->getException('aliyun'));
            $res = $e->getException('aliyun')->raw;
            if ($res['Code'] == 'isv.MOBILE_NUMBER_ILLEGAL') {
                return '手机号无效';
            }
            return '系统错误，发送失败';
        }
        return 0;

    }
}
