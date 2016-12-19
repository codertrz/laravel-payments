<?php

return [

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
     * 检查pingxx签名
     */
    'signature' => [
        'enable' => true,
        //public key 内容
        'pub_key_contents' => ''
    ],

];
