<?php

namespace SmartX\Controllers\Wx;

use Illuminate\Http\Request;
use SmartX\Controllers\BaseWxController;
use Validator;
use SmartX\Models\User;

class AuthController extends BaseWxController
{

    /*
     * 用户注册
     */
    public function registerUser(Request $request) {
        $data = $request->only('username', 'phone', 'password', 'name');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'username'    => 'required',
            'phone'    => 'required',
            'password'    => 'required',
            'name'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        return User::registerUser($data);
    }

    /*
     * 用户名密码登录
     */
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
            return $this->errorMessage(500, '账号或密码错误');
        }
    }

    /*
     * 手机号登录(微信绑定的手机号)
     */
    public function phoneBind(Request $request)
    {
        $wx_user = $this->wx_user;
        if (empty($wx_user)) {
            return $this->errorMessage(201, '请先登录');
        }
        $data = $request->only('encryptedData', 'iv');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'encryptedData'    => 'required',
            'iv'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        $data['session_key'] = $wx_user->session_key;
        return $this->wx->phoneBind($data);
    }

    /*
     * 完善用户
     */
    public function completeUser(Request $request) {
        $data = $request->only('username', 'phone', 'password', 'name', 'code');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'phone'    => 'required',
            'code'    => 'required',
            'username'    => 'required',
            'password'    => 'required',
            'name'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        return $this->wx->completeUser($data);
    }

    /*
     * 获取手机验证码
     */
    public function getVerifyCode(Request $request)
    {
        $data = $request->only('phone', 'action');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'phone'    => 'required',
            'action'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        return User::getVerifyCode($data);
    }

    /*
     * 验证验证码
     */
    public function verifyCode(Request $request) {
        $data = $request->only('phone', 'verify_code', 'action');
        $message = [
            'required' => ':attribute 不能为空',
            'numeric' => ':attribute 必须为数字'
        ];
        $validator = Validator::make($data, [
            'verify_code'    => 'required|numeric',
            'phone'    => 'required|numeric',
            'action'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        if (User::verifyCode($data)) {
            return $this->message();
        } else {
            return $this->errorMessage(422, '验证码错误');
        }
    }


    /*
     * 微信登录
     */
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

        return $this->wx->wxLogin($data['code']);
    }

    /*
     * 微信绑定
     */
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

        return $this->wx->bindUser($data['code'], $this->auth->user()->id);
    }

    /*
     * 微信解绑
     */
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
        return $this->wx->relieveBind($data['code']);
    }

    public function userInfo(Request $request)
    {
        return $this->message($this->auth->user());
    }

    public function saveUserInfo(Request $request) {
        $data = $request->only('avatar');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'code'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        return $this->wx->relieveBind($data['code']);
    }



    public function logout()
    {
        $this->auth->logout();

        return $this->message(['message' => '已退出登录']);
    }
}
