<?php namespace Beansme\Payments\Events\Payments;

use Beansme\Payments\Models\RefundPayment;
use Illuminate\Queue\SerializesModels;

class PaymentRefundSucceed {

    use SerializesModels;

    /**
     * @var RefundPayment
     */
    public $refund_payment;

    /**
     * PaymentRefunded constructor.
     * @param RefundPayment $refund_payment
     */
    public function __construct(RefundPayment $refund_payment)
    {
        $this->refund_payment = $refund_payment;
    }


}
