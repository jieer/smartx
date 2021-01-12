<?php

namespace SmartX\Middleware;

use Closure;
use SmartX\Controllers\BaseReturnTrait;
use SmartX\Models\WxApp;

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
        if (empty($value)) {
            $value = $request->header('APPID');
        }
        if (empty($value)) {
            $value = $request->header('appid');
        }
        if (!empty($value)) {
            $request->session()->regenerate();
            $request->session()->put('app_id', $value);
        }
        $value = $request->session()->get('app_id');
        $app = WxApp::find($value);
        if (empty($app)) {
            $app = WxApp::getDefault();
            if (empty($app)) {
                return $this->errorMessage(403,'该应用未认证');
            } else {
                $request->session()->regenerate();
                $request->session()->put('app_id', $app->id);
            }
        }
        return $next($request);
    }
}
