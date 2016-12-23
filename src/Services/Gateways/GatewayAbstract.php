<?php namespace Beansme\Payments\Services\Gateways;

use Beansme\Payments\Models\Payment;
use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Models\RefundPayment;
use Beansme\Payments\Services\Contracts\CanRefund;
use Beansme\Payments\Services\Contracts\NeedPay;

abstract class GatewayAbstract {

    protected static $config;

    public function __construct()
    {
        self::$config = config('payments.gateways.' . $this->configName());
    }

    public abstract function configName();

    public abstract function purchase(NeedPay $order);

    /**
     * @param $charge
     * @param Receipt $receipt
     * @return Payment
     */
    public abstract function persistTransaction($charge, Receipt $receipt);

    public abstract function fetchTransaction($payment_id);

    public abstract function fetchTransactionLists($parameters);

    public abstract function fetchRefund(CanRefund $payment);

    /**
     * @return RefundPayment
     */
    public abstract function persistRefund();

    public abstract function transfer();

    public abstract function refund(CanRefund $payment, $desc, $amount = null);

}
