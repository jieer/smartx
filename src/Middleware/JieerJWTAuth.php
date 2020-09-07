<?php

namespace Jieer\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth;

class JieerJWTAuth
{
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
            if (! auth('wx')->user()) {
                return response()->json([
                    'code' => 401,
                    'message' => '未查询到此用户'

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
