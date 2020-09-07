<?php

namespace SmartX\Middleware;

use Closure;
use SmartX\Controllers\BaseReturnTrait;

class WxAppHandle
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
        $value = $request->cookie('app_id');
        if (!empty($value)) {
            $request->session()->regenerate();
            $request->session()->put('app_id', $value);
        }
        $value = $request->session()->get('app_id');
        if (empty($value)) {
            return $this->errorMessage(401,'该应用未认证');
        }
        return $next($request);
    }
}
