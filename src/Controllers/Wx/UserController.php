<?php

namespace App\Http\Controllers\Wx;

use Illuminate\Http\Request;
use SmartX\Controllers\BaseWxController;
use Validator;
use SmartX\Models\User;
use App\Models\Content\SmxUserFollow;

class UserController extends BaseWxController
{

    public function userFollows(Request $request)
    {

        $data = $request->only('user_id');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'user_id'    => 'required|numeric',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(400, $validator->errors()->first());
        };
        $user_follows = SmxUserFollow::where('user_id', $data['user_id'])->orderBy('created_at', 'desc')->get();
        $user_ids = [];
        if (count($user_follows) > 0) {
            foreach ($user_follows as $user_follow) {
                array_push($user_ids, $user_follow->source_user_id);
            }
        }
        $users = User::whereIn('id', $user_ids)->get(['id','level','name', 'avatar']);
        return $this->message($users);
    }

    public function userFans(Request $request) {
        $data = $request->only('user_id');
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($data, [
            'user_id'    => 'required|numeric',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(400, $validator->errors()->first());
        };
        $user_follows = SmxUserFollow::where('source_user_id', $data['user_id'])->orderBy('created_at', 'desc')->get();
        $user_ids = [];
        if (count($user_follows) > 0) {
            foreach ($user_follows as $user_follow) {
                array_push($user_ids, $user_follow->source_user_id);
            }
        }
        $users = User::whereIn('id', $user_ids)->get(['id','level','name', 'avatar']);
        return $this->message($users);
    }

}
