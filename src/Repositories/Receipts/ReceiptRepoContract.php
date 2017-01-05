<?php namespace BTWay\Payments\Repositories\Receipts;


use BTWay\Payments\Models\Payment;
use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Models\RefundReceipt;
use BTWay\Payments\Protocol;

interface ReceiptRepoContract {

    public function setPaymentType($payment_type);

    public function getPaymentType();

    /**
     * @param $order_no
     * @param bool $by_order
     * @return Receipt|mixed
     */
    public function fetchReceipt($order_no, $by_order = true);

    /**
     * @param $payment_type
     * @param $user_id
     * @param $order_no
     * @param $amount
     * @param $subject
     * @param $body
     * @return Payment|mixed
     */
    public function initReceipt($user_id, $order_no, $amount, $subject, $body);

    public function fetchPaidPayment($receipt);

    /**
     * @param null $refund_id
     * @param null $receipt_id
     * @return RefundReceipt|mixed
     */
    public function fetchRefundReceipt($refund_id = null, $receipt_id = null);

    public function getAllRefundReceipts($status, $per_page = Protocol::PER_PAGE);

}
