<?php namespace Beansme\Payments\Services\Receipts;

use Beansme\Payments\Models\Payment;
use Pingpp\Charge;
use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Gateways\Exceptions\CanNotRefundException;
use Beansme\Payments\Services\Gateways\Exceptions\ChargeNotPayException;
use Beansme\Payments\Services\Gateways\GatewayAbstract;
use Illuminate\Database\Eloquent\Model;

class ReceiptService implements ReceiptServiceContract {

    /**
     * @var GatewayAbstract
     */
    private $gateway;

    /**
     * ReceiptServiceAbstract constructor.
     * @param GatewayAbstract $gateway
     */
    public function __construct(GatewayAbstract $gateway)
    {
        $this->gateway = $gateway;
    }


    /**
     * get or create order_no receipt
     * @param $order_no
     * @param bool $by_order
     * @return Receipt|Model
     */
    public function fetchReceipt($order_no, $by_order = true)
    {
        if ($order_no instanceof Receipt) {
            return $order_no;
        }

        if ($by_order) {
            return Receipt::query()->where('order_no', $order_no)->where('payment_type', $this->gateway->getPaymentType())->first();
        }

        return Receipt::query()->findOrFail($order_no);
    }

    public function initReceipt($user_id, $order_no, $amount, $subject, $body)
    {
        if ($exist_receipt = $this->fetchReceipt($order_no, $by_order = true)) {
            return $exist_receipt;
        }

        return Receipt::create([
            'id' => Protocol::generateId(),
            'user_id' => $user_id,
            'order_no' => $order_no,
            'subject' => $subject,
            'body' => $body,
            'payment_type' => $this->gateway->getPaymentType(),
            'amount' => $amount,
            'amount_refunded' => 0,
            'pay_status' => Protocol::STATUS_PAY_UNPAID,
            'refund_status' => Protocol::STATUS_REFUND_NONE,
            'invoice_status' => Protocol::STATUS_INVOICE_NONE
        ]);
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
        $receipt = $this->initReceipt($user_id, $order_no, $amount, $subject, $body);

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
        $receipt = $this->fetchReceipt($receipt, $by_order = false);

        return $receipt->isPaid() ?: call_user_func(function () use ($receipt) {
            $payment = $this->gateway->receiptIsPaid($receipt->getKey());
            if (!is_null($payment) && $payment instanceof Payment) {
                $receipt->setAsPaid($payment);
            }
            return $payment ? true : false;
        });
    }

    /**
     * @param $receipt
     * @param int $amount
     * @param string $desc
     * @return mixed
     * @throws CanNotRefundException
     */
    public function refund($receipt, $amount, $desc)
    {
        $receipt = $this->fetchReceipt($receipt, $by_order = false);

        if (!$receipt->canRefund()) {
            throw new CanNotRefundException();
        }

        $refund_charge = $this->gateway->refund($receipt->payment, $desc, $amount);

        return $refund_charge;
    }

    /**
     * @return GatewayAbstract
     */
    public function gateway()
    {
        return $this->gateway;
    }
}
