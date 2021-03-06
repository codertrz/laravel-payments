<?php namespace BTWay\Payments\Services\Gateways;

use BTWay\Payments\Models\Payment;
use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Models\RefundPayment;
use BTWay\Payments\Models\RefundReceipt;
use BTWay\Payments\Services\Contracts\CanRefund;

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

    public function getPaymentType()
    {
        return self::$config['payment_type'];
    }

    public function getRefundPaymentType()
    {
        return self::$config['refund_payment_type'];
    }


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

    public abstract function refund(RefundReceipt $refund);

    public abstract function fetchRefundTransaction($refund_id = null, $payment_id = null, $local = false);

    public abstract function finishRefund($refund_charge);

    public abstract function transactionIsRefunded($refund_charge_id);

}
