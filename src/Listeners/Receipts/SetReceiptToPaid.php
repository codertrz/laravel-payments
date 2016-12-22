<?php namespace Beansme\Payments\Listeners;

use Beansme\Payments\Events\Payments\PaymentPaid;
use Beansme\Payments\Models\Receipt;

class SetReceiptToPaid {

    public function __construct()
    {

    }

    public function handle(PaymentPaid $event)
    {
        $payment = $event->payment;

        if($payment->isP)

        /**
         * @var Receipt
         */
        $receipt = $payment->getRelationValue('receipt');

        if ($receipt) {
            $receipt->setAsPaid($payment);
        }
    }
}
