<?php

return [

    'auth_guard' => 'smart',

    'database' => [

        'connection' => '',

        'common_user_table' => 'common_user',

        'wx_app_table' => 'wx_app',

        'wx_user_table' => 'wx_user',

    ],
    'models' => [
        'common_user' => [
            'model' => Smartwell\Models\User::class,
            'select' => ['id','phone', 'name'],
            'modules' => ['id','username', 'phone', 'name', 'avatar', 'created_at']
        ],
        'wx_app' => [
            'model' => Smartwell\Models\WxApp::class,
            'select' => ['id','appid', 'name', 'type'],
            'modules' => ['id','appid', 'name', 'type', 'secret', 'token',
                'aes_key', 'mch_id', 'notify', 'remark', 'created_at']
        ],
        'wx_user' => [
            'model' => Smartwell\Models\WxUser::class,
            'select' => ['id', 'app_id', 'user_id', 'nickname', 'sex'],
            'modules' => ['id', 'app_id', 'user_id', 'openid', 'unionid',
                'nickname', 'headimgurl', 'sex', 'remark', 'label', 'is_black', 'created_at']
        ]
    ]


];