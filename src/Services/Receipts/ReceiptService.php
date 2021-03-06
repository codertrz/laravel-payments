<?php namespace BTWay\Payments\Services\Receipts;

use BTWay\Payments\Models\Payment;
use BTWay\Payments\Repositories\Receipts\ReceiptRepoContract;
use Pingpp\Charge;
use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Protocol;
use BTWay\Payments\Services\Gateways\Exceptions\CanNotRefundException;
use BTWay\Payments\Services\Gateways\Exceptions\ChargeNotPayException;
use BTWay\Payments\Services\Gateways\GatewayAbstract;
use Illuminate\Database\Eloquent\Model;

class ReceiptService implements ReceiptServiceContract {

    /**
     * @var GatewayAbstract
     */
    protected $gateway;

    /**
     * @var ReceiptRepoContract
     */
    protected $receiptRepo;

    /**
     * ReceiptServiceAbstract constructor.
     * @param GatewayAbstract $gateway
     * @param ReceiptRepoContract $receiptRepo
     */
    public function __construct(GatewayAbstract $gateway, ReceiptRepoContract $receiptRepo)
    {
        $this->gateway = $gateway;
        $this->receiptRepo = $receiptRepo;
        $this->receiptRepo->setPaymentType($this->gateway->getPaymentType());
    }


    /**
     * @param $user_id
     * @param $order_no
     * @param $amount
     * @param $subject
     * @param $body
     * @param null $channel
     * @return Charge|mixed
     */
    public function purchase($user_id, $order_no, $amount, $subject, $body, $channel = null)
    {
        $receipt = $this->receiptRepo->initReceipt($user_id, $order_no, $amount, $subject, $body);

        if ($this->isPaid($receipt)) {
            return $this->gateway->fetchTransaction($receipt->getPaymentId(), $local = false);
        }

        return $this->gateway->purchase($receipt, $channel);
    }

    /**
     * @param $receipt
     * @return boolean
     */
    public function isPaid($receipt)
    {
        $receipt = $this->receiptRepo->fetchReceipt($receipt, $by_order = false);

        return $receipt->isPaid() ?: call_user_func(function () use ($receipt) {
            $payment = $this->gateway->receiptIsPaid($receipt->getKey());
            if (!is_null($payment) && $payment instanceof Payment) {
                $receipt->setAsPaid($payment);
            }
            return $payment ? true : false;
        });
    }

    public function refund($order_no, $amount, $desc)
    {
        $refund_receipt = $this->applyRefund($order_no, $amount, $desc, $by_order = true);

        if (!$this->refundNeedAudit()) {
            return $this->approveRefund($refund_receipt);
        }

        return $refund_receipt;
    }

    protected function refundNeedAudit()
    {
        return config('payments.refund_audit', false);
    }

    public function applyRefund($receipt, $amount, $desc, $by_order = false)
    {
        $receipt = $this->receiptRepo->fetchReceipt($receipt, $by_order);

        if (!$this->isPaid($receipt)) {
            throw new ChargeNotPayException();
        }

        $amount = $amount ?: $receipt->getCanRefundAmount();

        if (!$receipt->canRefund($amount)) {
            throw new CanNotRefundException();
        }

        $refund_receipt = $receipt->setApplyRefund($amount, $desc);

        return $refund_receipt;
    }

    public function rejectRefund($refund_receipt, $memo)
    {
        $refund_receipt = $this->receiptRepo->fetchRefundReceipt($refund_receipt);

        $refund_receipt->setAsReject($memo);

        return $refund_receipt;
    }

    public function approveRefund($refund_receipt)
    {
        $refund_receipt = $this->receiptRepo->fetchRefundReceipt($refund_receipt);

        if (!$refund_receipt->canRequest()) {
            throw new \Exception('无法重复发起退款');
        }

        $refund_charge = $this->gateway->refund($refund_receipt);

        return $refund_receipt;
    }

    public function checkRefundSucceed($refund_receipt)
    {
        $refund_receipt = $this->receiptRepo->fetchRefundReceipt($refund_receipt);

        return $refund_receipt->isSucceed() ?: call_user_func(function () use ($refund_receipt) {
            $succeed = $this->gateway->transactionIsRefunded($refund_receipt->getRefundPaymentId());
            return $succeed;
        });
    }

    /**
     * @return GatewayAbstract
     */
    public function gateway()
    {
        return $this->gateway;
    }


}
