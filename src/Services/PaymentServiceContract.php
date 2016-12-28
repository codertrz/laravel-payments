<?php namespace Beansme\Payments\Services;

use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Contracts\NeedPay;

interface PaymentServiceContract {

    public function checkPaidSucceed($payment_id, $by_receipt = false);

    public function checkRefundSucceed($refund_id);

    public function refund($payment_id, $amount, $desc);

    public function fetchGatewayTransaction($payment_id);

    public function purchase(NeedPay $order, Receipt $receipt, $channel = null);


}
