<?php namespace BTWay\Payments\Listeners\Receipts;

use BTWay\Payments\Events\Payments\PaymentPaid;
use BTWay\Payments\Models\Receipt;

class FinishReceiptPurchase {

    public function __construct()
    {

    }

    public function handle(PaymentPaid $event)
    {
        $payment = $event->payment;
        /**
         * @var Receipt
         */
        $receipt = $payment->getRelationValue('receipt');

        if ($receipt) {
            $receipt->setAsPaid($payment);
        }
    }
}
