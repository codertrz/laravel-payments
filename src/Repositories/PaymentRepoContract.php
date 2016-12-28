<?php namespace Beansme\Payments\Repositories;

use Beansme\Payments\Models\Payment;
use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Services\Gateways\GatewayAbstract;

interface PaymentRepoContract {

    public function create(Receipt $receipt, GatewayAbstract $gateway);

    /**
     * @param $id
     * @return Payment
     */
    public function get($id);

    public function getByReceipt($receipt_id);

}
