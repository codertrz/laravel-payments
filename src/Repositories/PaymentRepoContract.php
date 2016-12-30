<?php namespace BTWay\Payments\Repositories;

use BTWay\Payments\Models\Payment;
use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Services\Gateways\GatewayAbstract;

interface PaymentRepoContract {

    public function create(Receipt $receipt, GatewayAbstract $gateway);

    /**
     * @param $id
     * @return Payment
     */
    public function get($id);

    public function getByReceipt($receipt_id);

}
