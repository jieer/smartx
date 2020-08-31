<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class SmartWellJWTAuth
{

    private $auth;

    public function __construct()
    {
        $this->auth = auth(config('smartwell.auth_guard'));
    }
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
            if (! $user = $this->auth->parseToken()->authenticate()) {  //获取到用户数据，并赋值给$user
                return response()->json([
                    'code' => 401,
                    'message' => 'user not found'

                ], 404);
            }
            return $next($request);

        } catch (TokenExpiredException $e) {

            return response()->json([
                'code' => 401,
                'message' => 'token 过期' , //token已过期
            ]);

        } catch (TokenInvalidException $e) {

            return response()->json([
                'code' => 401,
                'message' => 'token 无效',  //token无效
            ]);

        } catch (JWTException $e) {

            return response()->json([
                'code' => 401,
                'message' => '缺少token' , //token为空
            ]);

        }
    }
}
