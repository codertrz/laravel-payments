<?php

return [

    'default' => env('PAY_GATEWAY', 'pingxx'),

    'refund_audit' => env('PAY_GATEWAY_REFUND_AUDIT', false),

    'helper_functions' => [
        'get_user_openid' => '\BTWay\Payments\Protocol::getUserOpenId', // Class::staticFunction | [Class, function]
    ],
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

            'url_mobile_success' => '',
            'url_mobile_cancel' => '',
            'url_pc_success' => '',
            'url_pc_cancel' => '',

            /**
             * rsa
             */
            'signature' => [
                'enable' => env('PINGXX_RSA', true),
                //rsa key 内容
                'private_key_path' => env('PINGXX_PRIVATE_KEY_PATH', false),
                'public_key_path' => env('PINGXX_PUBLIC_KEY_PATH', false),
            ],

            //system setting
            'payment_type' => '\BTWay\Payments\Models\Payment',
            'refund_payment_type' => '\BTWay\Payments\Models\RefundPayment',
        ],
        'credits' => [],
        'wallet' => []
    ]


];
