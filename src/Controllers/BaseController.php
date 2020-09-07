<?php

namespace SmartX\Controllers;

use Illuminate\Routing\Controller;
use SmartX\Controllers\BaseReturnTrait;

class BaseController extends Controller
{
    use BaseReturnTrait;

    protected $auth;

    public function __construct()
    {
        $this->auth = auth(config('smartx.auth_guard'));
    }

}
