<?php namespace Beansme\Payments\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

    protected $listen = [
        'Beansme\Payments\Events\Payments\PaymentPaid' => [
            'Beansme\Payments\Listeners\Receipts\FinishReceiptPurchase'
        ]
    ];

}
