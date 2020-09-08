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
//            'password'    => 'required',
        ], $message);
        if ($validator->fails()) {
            return $this->errorMessage(422, $validator->errors()->first());
        };

        if ($this->wx_app->tyoe === 3) {
            //公众号
            $code_url = $this->wx->getCodeUrl(empty($data['type']) ? 0:$data['type'], empty($data['scene_id']) ? 'open':$data['scene_id']);
        }  else {
            if (empty($data['type'])) {
                //数量多的临时小程序码
                //optional中应该包含page参数指向小程序页面
                $res = $this->wx->getMiniCodeB($data['scene'], $data['optional']);
            } elseif ($data['type'] === 1) {
                //永久小程序码
                $res = $this->wx->getMiniCodeA($data['path'], $data['optional']);
            } else {
                //二维码
                $res = $this->wx->getMiniQrCode($data['path'], empty($data['width']) );

            }
            return $this->message([
                'qrcode_base64' => base64_encode($res),
            ]);
        }

        return $this->message([
            'qrcode_url' => $code_url
        ]);
    }


}
