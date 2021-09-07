<?php

namespace SmartX\Middleware;

use Closure;
use SmartX\Controllers\BaseReturnTrait;
use SmartX\Models\WxUser;

class UserHandel
{
    use BaseReturnTrait;
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            $sessionKey = $request->header('SESSIONKEY');
            if (empty($sessionKey)) {
                return $this->errorMessage(401, '未登录，缺少sessionKey');
            }
            $str = decrypt($sessionKey);
            if (empty($str)) {
                return $this->errorMessage(401, '未登录, 缺少sessionKey');
            }
            list($wx_id, $timeout) = explode("\t", $str);
            if (empty($wx_id) || ($timeout < time())) {
                $request->header('SESSIONKEY', '');
                return $this->errorMessage(401, '未登录, sessionKey 过期');
            }
            $wx_user = WxUser::find($wx_id);

            if (empty($wx_user)) {
                return $this->errorMessage(401, '未登录, sessionKey 无效');
            }
            return $next($request);

        } catch (Exception $e) {
            return $this->errorMessage(401,'未登录, sessionKey 无效');
        }
    }
}
