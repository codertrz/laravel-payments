<?php namespace Beansme\Payments\Events\Payments;

use Beansme\Payments\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentPaid {

    use SerializesModels;

    /**
     * @var Payment
     */
    public $payment;

    /**
     * PaymentPaid constructor.
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

}
