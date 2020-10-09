<?php

namespace SmartX\Services;

use SmartX\Models\WxUser;

class UserService
{
    public static function currentUser() {
        if (empty(auth(config('smartx.auth_guard')))) {
            return new \ArrayObject([]);
        }
        $user = auth(config('smartx.auth_guard'));
    }
}