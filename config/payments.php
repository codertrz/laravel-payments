<?php

return [

    'default' => env('PAY_GATEWAY', 'pingxx'),
    /*
    |--------------------------------------------------------------------------
    | 内置路由的属性
    |--------------------------------------------------------------------------
    |
    | 如果是web应用建议'middleware'为'web',
    | 如果是api应用(无session),建议'middleware'为'api'。
    |
    */
    'routeAttributes' => [
        'prefix' => 'api/gateway/payments',
        'middleware' => 'api',
    ],

    /**
     * 支付渠道
     */
    'gateways' => [
        'pingxx' => [
            'name' => 'Pingpp',
            'api_key' => env(env('PINGXX_ACCOUNT_ENV', 'TEST') . '_PINGXX_API_KEY'),
            'app_id' => env('PINGXX_APP_ID'),
            'live' => env('PINGXX_LIVE_MODE', false),
            /**
             * rsa
             */
            'signature' => [
                'enable' => env('PINGXX_RSA', true),
                //rsa key 内容
                'private_key' => file_get_contents(env('PINGXX_PRIVATE_KEY', '')),
                'public_key' => env('PINGXX_PUBLIC_KEY', true),
            ],

        ]
    ]


];
