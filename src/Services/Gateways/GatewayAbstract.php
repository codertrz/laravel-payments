<?php namespace Beansme\Payments\Services\Gateways;

use Beansme\Payments\Models\Payment;
use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Models\RefundPayment;
use Beansme\Payments\Services\Contracts\CanRefund;

abstract class GatewayAbstract {

    /**
     * Init
     */
    /**
     * @var array $config
     */
    protected static $config;

    public function __construct()
    {
        self::$config = config('payments.gateways.' . $this->configName());
    }

    public abstract function configName();

    /**
     * Purchase
     */

    /**
     * @param Receipt $receipt
     * @param null $channel
     * @return mixed
     */
    public abstract function purchase(Receipt $receipt, $channel = null);

    /**
     * @param $charge
     * @param Receipt $receipt
     * @return Payment
     */
    public abstract function persistTransaction($charge, Receipt $receipt);

    public abstract function fetchTransaction($payment_id, $local = true);

    public abstract function fetchTransactionLists($parameters, $local = false);

    public abstract function isPaid($charge);

    public abstract function finishPurchase($charge);

    public abstract function transactionIsPaid($payment_id);

    public abstract function receiptIsPaid($receipt_id);

    /**
     * Transfer
     */
    public abstract function transfer();

    /**
     * Refund
     */
    /**
     * @return RefundPayment
     */
    public abstract function persistRefund($refund_charge);


    public abstract function refund(CanRefund $payment, $desc, $amount = null);

    public abstract function fetchRefundTransaction(CanRefund $payment, $local = false, $refund_id = null);

    public abstract function finishRefund($refund_charge);

}
