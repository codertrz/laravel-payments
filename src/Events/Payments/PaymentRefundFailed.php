<?php namespace Beansme\Payments\Events\Payments;

use Illuminate\Queue\SerializesModels;

class PaymentRefundFailed {

    use SerializesModels;

    /**
     * @var PaymentRefunded
     */
    public $paymentRefunded;

    /**
     * PaymentRefundFailed constructor.
     * @param PaymentRefunded $paymentRefunded
     */
    public function __construct(PaymentRefunded $paymentRefunded)
    {
        $this->paymentRefunded = $paymentRefunded;
    }


}
