<?php

namespace Smartwell\Middleware;

use Closure;

class WxAppHandle
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

        $value = $request->session()->get('app_id');
        if (empty($value)) {
            $value = $request->cookie('app_id');
            if (!empty($value)) {
                $request->session()->regenerate();
                $request->session()->put('app_id', $value);
            }
        }
        if (empty($value)) {
            return response()->json([
                'code' => 401,
                'message' => '该应用未认证'

            ], 404);
        }
        return $next($request);
    }
}
