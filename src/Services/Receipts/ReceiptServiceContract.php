<?php namespace BTWay\Payments\Services\Receipts;

use Pingpp\Charge;

interface ReceiptServiceContract {

    /**
     * @param $user_id
     * @param $order_no
     * @param $amount
     * @param $subject
     * @param $body
     * @param null $channel
     * @return Charge|mixed
     */
    public function purchase($user_id, $order_no, $amount, $subject, $body, $channel = null);

    /**
     * @param $receipt_id
     * @return bool
     */
    public function isPaid($receipt_id);

    /**
     * @param $receipt_id
     * @param int $amount
     * @param string $desc
     * @return mixed
     */
    public function refund($receipt_id, $amount, $desc);

    public function gateway();


}
