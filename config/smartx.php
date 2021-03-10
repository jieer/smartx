<?php

return [

    'auth_guard' => 'wx',

    'database' => [

        'connection' => '',

        'common_user_table' => 'common_user',

        'user_club_table' => 'common_user_group',

        'wx_app_table' => 'wx_app',

        'wx_user_table' => 'wx_user',

        'user_id_table' => 'user_id',

    ],
    'models' => [
        'common_user' => [
            'model' => SmartX\Models\User::class,
            'select' => ['id','phone', 'name'],
            'modules' => ['id','username', 'phone', 'name', 'avatar', 'created_at']
        ],
        'common_user_club' => [
            'model' => SmartX\Models\UserClub::class,
            'select' => ['id','type', 'name'],
            'modules' => ['id', 'name', 'icon_path', 'allow_browse', 'allow_posted', 'allow_comment', 'allow_delete', 'created_at']
        ],
        'wx_app' => [
            'model' => SmartX\Models\WxApp::class,
            'select' => ['id','appid', 'name', 'type'],
            'modules' => ['id','appid', 'name', 'type', 'secret', 'token',
                          'aes_key', 'mch_id', 'notify', 'remark', 'created_at']
        ],
        'wx_user' => [
            'model' => SmartX\Models\WxUser::class,
            'select' => ['id', 'app_id', 'user_id', 'nickname', 'sex'],
            'modules' => ['id', 'app_id', 'user_id', 'openid', 'unionid',
                          'nickname', 'headimgurl', 'sex', 'remark', 'label', 'is_black', 'created_at']
        ]

    ],
    'directory' => [
        'controller' => app_path('Http/Controllers/Wx'),
    ],

    'route' => [

        'prefix' => 'wx',

        'namespace' => 'App\\Http\\Controllers\\Wx',

        'middleware' => ['api', 'smartx'],
    ],
    'report_reason' => [
        '5'  => '欺诈',
        '10' => '色情',
        '15' => '诱导行为',
        '20' => '不实信息',
        '25' => '违法犯罪',
        '30' => '骚扰',
        '35' => '其他',
    ],



];