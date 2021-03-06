<?php namespace BTWay\Payments\Events\Payments;

use BTWay\Payments\Models\RefundPayment;
use Illuminate\Queue\SerializesModels;

class PaymentRefundFailed {

    use SerializesModels;

    /**
     * @var PaymentRefundSucceed
     */
    public $refund_payment;

    /**
     * PaymentRefundFailed constructor.
     * @param RefundPayment $payment_refunded
     */
    public function __construct(RefundPayment $payment_refunded)
    {
        $this->refund_payment = $payment_refunded;
    }


}
