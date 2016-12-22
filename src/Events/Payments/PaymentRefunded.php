<?php namespace Beansme\Payments\Events\Payments;

use Beansme\Payments\Models\RefundPayment;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded {

    use SerializesModels;

    /**
     * @var RefundPayment
     */
    public $refundPayment;

    /**
     * PaymentRefunded constructor.
     * @param RefundPayment $refundPayment
     */
    public function __construct(RefundPayment $refundPayment)
    {
        $this->refundPayment = $refundPayment;
    }


}
