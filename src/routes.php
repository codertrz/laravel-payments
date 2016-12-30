<?php

$route_attribute = array_merge(
    [
        'prefix' => 'api/gateway/payments',
        'namespace' => 'BTWay\Payments\Http\Controllers',
    ],
    config('payments.routeAttributes', []));


Route::group($route_attribute, function () {
    Route::group(['prefix' => 'pingxx', 'middleware' => 'payments.auth.pingxx'], function () {
        Route::post('paid', 'PingxxNotifyController@paid');
    });
});
