<?php

namespace SmartX\Controllers;

use Illuminate\Http\Request;
use SmartX\Models\WxApp;
use SmartX\Models\WxUser;
use SmartX\WX\Wx;
use SmartX\Controllers\BaseController;

class BaseWxController extends BaseController
{

    private $app_id;
    private $wx_app;
    private $wx;
    private $sessionKey;
    private $wx_user;

    public function __get($name)
    {
        $this->initParams();
        return $this->$name;

    }

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->sessionKey = request()->header('SESSIONKEY');
        $this->wx_user = $this->wxUser();
    }

    protected function initParams() {

        $this->app_id = empty(session('app_id')) ? 0:session('app_id');
        $this->wx_app = WxApp::find($this->app_id);
        $this->wx = new Wx($this->wx_app);

    }

    protected function wxUser() {
        if (!empty($this->wx_user)) {
            return $this->wx_user;
        }
        if (empty($this->sessionKey)) {
            return false;
        }
        $str = decrypt($this->sessionKey);
        if (empty($str)) {
            return false;
        }
        list($wx_id, $session_key, $timeout) = explode("\t", $str);
        if (empty($wx_id) || empty($session_key) || ($timeout < time())) {
            $this->sessionKey = NULL;
            return false;
        }
        $wx_user = WxUser::find($wx_id);
        if ($wx_user) {
            $this->wx_user = $wx_user;
            return $wx_user;
        }
        return false;
    }

}
