<?php namespace Beansme\Payments\Services;

use Beansme\Payments\Protocol;

interface PaymentServiceContract {

    public function checkPaidSucceed($payment_id);

    public function checkRefundSucceed($refund_id);

    public function refund($payment_id, $amount);

}
