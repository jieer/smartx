<?php

return [

    'auth_guard' => 'smart',

    'database' => [

        'connection' => '',

        'wx_user_table' => 'smx_wx_user',

        'common_user_table' => 'smx_common_user',

        'wx_app_table' => 'smx_wx_app',
    ],
    'models' => [
        'wx_user' => [
            'model' => Smartwell\Models\WxUser::class,
            'modules' => ['id','nickname']
        ]
    ]


];