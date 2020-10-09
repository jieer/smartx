<?php

namespace SmartX\Controllers\Wx;

use Illuminate\Http\Request;
use SmartX\Controllers\BaseWxController;
use Validator;
use SmartX\Models\User;
use SmartX\Models\WxUser;
use App\Models\Content\SmxUserFollow;
use App\Smx\Services\WXService;
use App\Models\Sess;

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
        $data = $request->only('code', 'inviter_id');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'code'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        return $this->wx->wxLogin($data);
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
        return $this->wx->userInfo();
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

    public function otherUserInfo(Request $request)
    {
        $id = $request->input('user_id');

        $user = User::find($id, ['id','level','name', 'avatar', 'can_follow']);

        if (empty($user)) {
            return $this->message($user);
        }

        if ($this->auth->user()->id == $id) {
            $user->is_own = 1;
        } else {
            $user->is_own = 0;
        }

        $user->follow_count = SmxUserFollow::getUserFollowCount($id);
        $user->fans_count = SmxUserFollow::getFollowUserCount($id);
        $follow = SmxUserFollow::where('user_id', $this->auth->user()->id)->where('source_user_id', $id)->first();
        if (!empty($follow) || $this->auth->user()->id == $id) {
            $user->is_follow = 1;
        } else {
            $user->is_follow = 0;
        }
        return $this->message($user);

    }

    public function follow(Request $request) {
        $data = $request->only('user_id');
        $message = [
            'user_id' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'user_id'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };
        $user = User::find($data['user_id']);
        if (empty($user) || $user->id == $this->auth->user()->id) {
            return $this->errorMessage(500, '你要关注的用户不存在');
        }
        $user_follow = SmxUserFollow::where('user_id', $this->auth->user()->id)
            ->where('source_user_id', $data['user_id'])
            ->first();
        if (empty($user_follow)) {
            $rows = SmxUserFollow::insert([
                'user_id' => $this->auth->user()->id,
                'source_user_id' => $data['user_id']
            ]);
        } else {
            $rows = SmxUserFollow::where('user_id', $this->auth->user()->id)
                ->where('source_user_id', $data['user_id'])
                ->delete();
        }
        if ($rows > 0) {
            return $this->message();
        }
        return $this->errorMessage(500, '变更失败');
    }



    public function logout()
    {
        $this->auth->logout();

        return $this->message(['message' => '已退出登录']);
    }

    public function officialAccountLogin(Request $request)
    {
        $token = time().rand(100000,999999);
        $sess = new Sess;
        $sess->token = $token;
        $sess->addtime = date('Y-m-d H:i:s');
        $sess->status = 0;
        $sess->app_id = $this->app_id;
        $sess->save();

        $url = $this->wx->getCodeUrl(0, 'action=login&session_id=' . $token);

        return $this->message(['url' => $url, 'session_id' => $sess->id]);
    }

    public function officialShouquan(Request $request) {
        $data = $request->only('session_id');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'session_id'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        $sess = Sess::find($data['session_id']);
        if (empty($sess)) {
            return $this->errorMessage(410, '二维码无效');
        }
        if (!empty($sess->logintime)) {
            Sess::where('id', $sess->id)->update(['status' => 9]);
            return $this->errorMessage(410, '二维码无效');
        }
        if ((time() - strtotime($sess->addtime) - 1800) > 0) {
            Sess::where('id', $sess->id)->update(['status' => 2]);
            return $this->errorMessage(410, '二维码已过期');
        }
        if ($sess->status == 1) {
            $wx_user = WxUser::find($sess->wx_user_id);
            if (empty($wx_user)) {
                return $this->errorMessage(500, '微信用户未创建');
            }
            $user = User::find($wx_user->user_id);
            if (empty($user)) {
                return $this->message([], WxUser::setSession($wx_user));
            }
            Sess::where('id', $sess->id)->update(['userid' => $user->id]);
            return $this->message([
                'access_token' => auth(config('smartx.auth_guard'))->login($user),
                'ttl' => User::getTTL(),
                'refresh_ttl' => User::getRefreshTTL(),
                'user' => $user
            ], WxUser::setSession($wx_user)
            );
        } elseif($sess->status == 0) {
            return $this->errorMessage(202, '尚未扫码');
        } else {
            return $this->errorMessage(410, '无法使用');
        }

    }


}
