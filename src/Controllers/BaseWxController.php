<?php

namespace SmartX\Controllers;

use SmartX\Models\WxApp;
use SmartX\WX\Wx;
use SmartX\Controllers\BaseController;

class BaseWxController extends BaseController
{
    private $app_id;
    private $wx_app;
    private $wx;

    public function __get($name)
    {
        $this->initParams();
        return $this->$name;

    }

    protected function initParams() {

        $this->app_id = empty(session('app_id')) ? 0:session('app_id');
        $this->wx_app = WxApp::find($this->app_id);
        $this->wx = new Wx($this->wx_app);
    }

}
