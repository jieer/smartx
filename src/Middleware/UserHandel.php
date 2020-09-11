<?php

namespace SmartX\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth;
use SmartX\Controllers\BaseReturnTrait;

class UserHandel
{
    use BaseReturnTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            if (! JWTAuth::getToken()) {
                return $this->errorMessage(401,'未登录');
            } else if (JWTAuth::getToken() == 'temp_token') {
                return $this->errorMessage(401,'您未绑定手机号，没有权限访问');
            }
            return $next($request);

        } catch (TokenExpiredException $e) {
            return $this->errorMessage(401,'token 过期');

        } catch (TokenInvalidException $e) {
            return $this->errorMessage(401,'token 无效');

        } catch (JWTException $e) {
            return $this->errorMessage(401,'缺少token');

        }
    }
}
