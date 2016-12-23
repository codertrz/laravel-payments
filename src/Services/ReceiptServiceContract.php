<?php namespace Beansme\Payments\Services;

use Beansme\Payments\Services\Contracts\NeedPay;
use Beansme\Payments\Services\Gateways\GatewayAbstract;

interface ReceiptServiceContract {

    public function checkout(NeedPay $order, GatewayAbstract $gateway, $extra = []);

    /**
     * @param $receipt_id
     * @return boolean
     */
    public function checkPaid($receipt_id);

    public function refund($receipt_id);

    /**
     * @param $receipt_id
     * @return boolean
     */
    public function checkRefund($receipt_id);

}
