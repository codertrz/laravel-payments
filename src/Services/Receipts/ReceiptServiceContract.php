<?php namespace Beansme\Payments\Services\Receipts;

use Beansme\Payments\Models\Payment;

interface ReceiptServiceContract {

    public function purchase($user_id, $order_no, $amount, $subject, $body, $channel = null);

    /**
     * @param $receipt_id
     * @return bool
     */
    public function isPaid($receipt_id);

    /**
     * @param $receipt_id
     * @param Payment $payment
     * @return mixed
     */
    public function finishPurchase($receipt_id, Payment $payment);

    /**
     * @param $receipt_id
     * @param int $amount
     * @param string $desc
     * @return mixed
     */
    public function refund($receipt_id, $amount, $desc);


}
