<?php

namespace SmartX\Services;

use Illuminate\Support\Facades\Cache;
use SmartX\Models\VerifyCode;

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
        if (time() - strtotime($verify->created_at) > $verify->ttl) {
            return 2;
        }
        return 1;
    }
}
