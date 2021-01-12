<?php

namespace SmartX\Controllers\Wx;

use Illuminate\Http\Request;
use SmartX\Controllers\BaseWxController;
use SmartX\Services\CommonService;
use Validator;
use SmartX\Models\User;
use SmartX\Models\WxUser;
use App\Models\Content\SmxUserFollow;
use App\Smx\Services\WXService;
use App\Models\Sess;
use SmartX\Services\VerifyCodeService;
use SmartX\Models\Id;
use Illuminate\Support\Facades\Hash;
use EasyWeChat\Kernel\Support\File;
use Storage;
use App\Models\Qrcode;
use App\Services\AliyunOssService;

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
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(401, '请先登录');
        }
        $data = $request->only('encryptedData', 'iv', 'inviter_id');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'encryptedData'    => 'required',
            'iv'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(401, '请先登录');
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
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(400, $validator->errors()->first());
        };
        if (!CommonService::verifPhone($data['phone'])) {
            return $this->errorMessage(400, '无效的手机号');
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
            return $this->errorMessage(400, $validator->errors()->first());
        };
        return User::verifyCode($data);
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
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(400, $validator->errors()->first());
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
            return $this->errorMessage(400, $validator->errors()->first());
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
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'user_id'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(400, $validator->errors()->first());
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

    public function officialAccountH5Login(Request $request)
    {
        $token = time().rand(100000,999999);
        $sess = new Sess;
        $sess->token = $token;
        $sess->addtime = date('Y-m-d H:i:s');
        $sess->status = 0;
        $sess->app_id = $this->app_id;
        $sess->save();

        $url = $this->wx->getCodeUrl(0, 'action=login&session_id=' . $token);

        return $this->message(['url' => $url, 'session_id' => $token]);
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
            return $this->errorMessage(400, $validator->errors()->first());
        };

        $sess = Sess::where('token', $data['session_id'])->where('app_id', $this->app_id)->first();
        if (empty($sess)) {
            return $this->errorMessage(200, new \ArrayObject(['status' => 410, 'message'=>'二维码无效']));
        }
        if (!empty($sess->logintime)) {
            Sess::where('id', $sess->id)->update(['status' => 9]);
            return $this->errorMessage(200, new \ArrayObject(['status' => 410, 'message'=>'二维码无效']));
        }
        if ((time() - strtotime($sess->addtime) - 1800) > 0) {
            Sess::where('id', $sess->id)->update(['status' => 2]);
            return $this->errorMessage(200, new \ArrayObject(['status' => 410, 'message'=>'二维码无效']));
        }
        if ($sess->status == 1) {
            $wx_user = WxUser::find($sess->wx_user_id);
            if (empty($wx_user)) {
                return $this->errorMessage(500, '服务器错误');
            }
            $user = User::find($wx_user->user_id);
            if (empty($user)) {
                return $this->message((object)null, WxUser::setSession($wx_user));
            }
            Sess::where('id', $sess->id)->update(['userid' => $user->id]);
            return $this->message([
                'access_token' => auth(config('smartx.auth_guard'))->login($user),
                'ttl' => User::getTTL(),
                'refresh_ttl' => User::getRefreshTTL(),
                'user' => $user,
            ], WxUser::setSession($wx_user)
            );
        } elseif($sess->status == 0) {
            return $this->errorMessage(200, new \ArrayObject(['status' => 202, 'message'=>'尚未使用']));
        } else {
            return $this->errorMessage(200, new \ArrayObject(['status' => 410, 'message'=>'二维码无效']));
        }

    }

    public function officialAccountMobileAuth(Request $request)
    {
        $callback_url = $request->input('callback_url');
        if (empty($callback_url)) {
            return $this->errorMessage(500, 'callback_url不能为空');
        }
        $res = $this->ew_app->oauth->scopes(['snsapi_userinfo'])
            ->redirect($callback_url);
        return $this->message(['url' => $res]);
    }

//    public function OfficialAccountCallback(Request $request) {
//        $data = $request->only('app_id', 'code', 'state');
//        $message = [
//            'required' => ':attribute 不能为空',
//        ];
//        $validator = Validator::make($data, [
//            'code'    => 'required',
//            'app_id'    => 'required',
//        ], $message);
//        if ($validator->fails()) {
//            return $this->errorMessage(400, $validator->errors()->first());
//        };
//        $app = WXService::getApp($data['app_id']);
//        $off_user = $app->oauth->userFromCode($data['code'])->getRaw();
//        if (empty($off_user['openid'])) {
//            return $this->errorMessage(500, '连接微信失败');
//        }
//        return WxUser::wxOffLogin($off_user, $data['app_id']);
//    }

    public function OfficialAccountLogin(Request $request) {
        $wx_user = $this->wx_user;
        if (!empty($wx_user)) {
            $user = User::find($wx_user->user_id);
            if (empty($user)) {
                return $this->message(new \ArrayObject(), WxUser::setSession($wx_user));
            }
            return $this->message([
                'access_token' => auth(config('smartx.auth_guard'))->login($user),
                'ttl' => User::getTTL(),
                'refresh_ttl' => User::getRefreshTTL(),
                'user' => $user
            ], WxUser::setSession($wx_user));
        }
        $data = $request->only('code', 'state');
        if (empty($data['code'])) {
            return $this->errorMessage(500, 'code 不能为空');
        }
//        if (empty($data['state'])) {
//            return $this->errorMessage(500, 'state 不能为空');
//        }


        $off_user = $this->ew_app->oauth->userFromCode($data['code'])->getRaw();
        if (empty($off_user['openid'])) {
            return $this->errorMessage(500, '连接微信失败');
        }
        return WxUser::wxOffLogin($off_user, $this->app_id);
    }

    public function addPhone(Request $request) {
        $data = $request->only('phone', 'verify_code', 'action');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'phone'    => 'required',
            'verify_code'    => 'required',
            'action'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(400, $validator->errors()->first());
        };
        if (!CommonService::verifPhone($data['phone'])) {
            return $this->errorMessage(400, '无效的手机号');
        };
        $result = VerifyCodeService::verify($data['action'], $data['phone'], $data['verify_code']);
        if ($result === 1) {
            $user = User::where('phone', $data['phone'])->first();
            if  (!empty($user)) {
                WxUser::where('id', $this->wx_user->id)->update(['user_id'=> $user->id]);
            } else {
                $user = new User();
                $user->id = Id::getId($data['phone']);
                $user->name    = $this->wx_user->nickname;
                $user->avatar    = str_replace('http://', 'https://', $this->wx_user->headimgurl);
                $user->created_at = date('Y-m-d H:i:s');
                $user->phone = $data['phone'];
                $user->username = $data['phone'];
                $user->password = Hash::make($data['phone']);
                $user->save();
            }
            $user = User::find($user->id);
            if (empty($user)) {
                return $this->errorMessage(500, '注册失败，请重试');
            } else {
                return $this->message([
                    'access_token' => auth(config('smartx.auth_guard'))->login($user),
                    'ttl' => User::getTTL(),
                    'refresh_ttl' => User::getRefreshTTL(),
                    'user' => $user
                ], WxUser::setSession($this->wx_user));
            }
        } elseif ($result === 2) {
            return $this->errorMessage(400, '验证码过期');
        } else {
            return $this->errorMessage(400, '验证码无效');
        }

    }

    public function getQrCode(Request $request) {
        $data = $request->only('type','scene_id', 'path', 'scene', 'optional', 'width');
        $message = [
            'required' => ':attribute 不能为空',
            'numeric' => ':attribute 必须为数字'
        ];
        $validator = Validator::make($data, [
            'type'    => 'required|numeric',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(400, $validator->errors()->first());
        };
        $qrcode = Qrcode::where('type', $data['type'])
            ->where('app_id', $this->app_id)
            ->where('optional', json_encode($data, true))
            ->first();
        if (!empty($qrcode)) {
            return $this->message([
                'qrcode_url' => AliyunOssService::signUrl($qrcode->path, '')
            ]);
        }
        if (empty($data['type'])) {
            //数量多的临时小程序码
            //optional中应该包含page参数指向小程序页面
            $res = $this->wx->getMiniCodeB($data['scene'], $data['optional']);
        } elseif ($data['type'] === 1) {
            //永久小程序码
            $res = $this->wx->getMiniCodeA($data['path'], $data['optional']);
        } else {
            //二维码
            $res = $this->wx->getMiniQrCode($data['path'], empty($data['width']));

        }

        if ($res instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $contents = $res->getBody()->getContents();
            $filename = md5($contents);
            $filename .= File::getStreamExt($contents);
            $path = 'qrcode/' . $this->app_id . '/' . date('YmdHis') . '/' . $filename;

            Storage::put($path, $contents);
            Qrcode::insert([
                'type' => $data['type'],
                'app_id' => $this->app_id,
                'path' => $path,
                'optional' => json_encode($data, true)
            ]);
            return $this->message([
                'qrcode_url' => AliyunOssService::signUrl($path, '')
            ]);
        } else {
            return $this->errorMessage($res['errcode']. $res['errmsg']);
        }
    }



}
