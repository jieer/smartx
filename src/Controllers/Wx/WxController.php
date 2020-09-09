<?php

namespace SmartX\Controllers\Wx;

use Illuminate\Http\Request;
use SmartX\Controllers\BaseWxController;
use Validator;

class WxController extends BaseWxController
{

    public function getQrCode(Request $request)
    {

        $data = $request->only('type','scene_id', 'path', 'scene', 'optional', 'width');
        $message = [
            'required' => ':attribute 不能为空',
            'numeric' => ':attribute 必须为数字'
        ];
        $validator = Validator::make($data, [
            'type'    => 'required|numeric',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

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
            return $this->message([
                'qrcode_base64' => 'data:image/jpeg;base64,' . base64_encode($res),
            ]);
        } else {
            return $this->errorMessage($res['errcode']. $res['errmsg']);
        }

    }

}
