<?php

namespace SmartX\Controllers\Wx;

use Illuminate\Http\Request;
use SmartX\Controllers\BaseWxController;
use Validator;

class AuthController extends BaseWxController
{

    public function login(Request $request) {
        $data = $request->only('username','phone','password');
        $message = [
            'required' => ':attribute 不能为空',
            'numeric' => ':attribute 必须为数字'
        ];
        $validator = Validator::make($data, [
            'username'    => 'required',
            'password'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        $token = $this->auth->attempt($data);

        if ($token) {
            return $this->message(['access_token' => $token]);
        } else {
            return $this->errorMessage('账号或密码错误');
        }
    }

    public function wxLogin(Request $request) {
        $data = $request->only('code');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'code'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        $res = $this->wx->wxLogin($data['code']);
        if ($res['status'] == 0) {
            return $this->message(['access_token' => $res['token']]);
        } else {
            return $this->errorMessage($res['status'], $res['msg']);
        }
    }

    public function bindUser(Request $request) {
        $data = $request->only('code');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'code'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        $res = $this->wx->bindUser($data['code'], $this->auth->user()->id);
        if ($res['status'] == 0) {
            return $this->message([]);
        } else {
            return $this->errorMessage($res['status'], $res['msg']);
        }
    }

    public function relieveBind(Request $request) {
        $data = $request->only('code');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'code'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        $res = $this->wx->relieveBind($data['code']);
        if ($res['status'] == 0) {
            return $this->message([]);
        } else {
            return $this->errorMessage($res['status'], $res['msg']);
        }
    }



    public function logout()
    {
        $this->auth->logout();

        return $this->message(['message' => '已退出登录']);
    }
}
