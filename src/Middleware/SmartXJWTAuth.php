<?php

namespace SmartX\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth;
use SmartX\Controllers\BaseReturnTrait;

class SmartXJWTAuth
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
            if (! auth(auth('smartx.auth_guard'))->user()) {
                return $this->errorMessage(401,'未登录');
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
