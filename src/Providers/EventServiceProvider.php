<?php namespace BTWay\Payments\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

    protected $listen = [
        'BTWay\Payments\Events\Payments\PaymentPaid' => [
            'BTWay\Payments\Listeners\Receipts\FinishReceiptPurchase'
        ]
    ];

}
