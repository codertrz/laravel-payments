<?php namespace BTWay\Payments\Repositories;

use BTWay\Payments\Models\Receipt;

interface ReceiptRepoContract {

    /**
     * @param $receipt_id
     * @return Receipt
     */
    public function get($receipt_id);

    /**
     * @param $order_no
     * @return Receipt
     */
    public function getByOrder($order_no);

}
