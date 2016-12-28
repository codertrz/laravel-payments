<?php namespace Beansme\Payments\Services;

use Beansme\Payments\Models\Payment;
use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Repositories\PaymentRepoContract;
use Beansme\Payments\Repositories\RefundRepoContract;
use Beansme\Payments\Services\Contracts\NeedPay;
use Beansme\Payments\Services\Gateways\GatewayAbstract;

class PaymentService implements PaymentServiceContract {

    /**
     * @var PaymentRepoContract
     */
    private $paymentRepo;
    /**
     * @var RefundRepoContract
     */
    private $refundRepo;
    /**
     * @var GatewayAbstract
     */
    private $gateway;

    /**
     * PaymentService constructor.
     * @param PaymentRepoContract $paymentRepo
     * @param RefundRepoContract $refundRepo
     * @param GatewayAbstract $gateway
     */
    public function __construct(PaymentRepoContract $paymentRepo, RefundRepoContract $refundRepo, GatewayAbstract $gateway)
    {
        $this->paymentRepo = $paymentRepo;
        $this->refundRepo = $refundRepo;
        $this->gateway = $gateway;
    }

    public function checkPaidSucceed($payment_id, $by_receipt = false)
    {
        if ($by_receipt) {
            $receipt_id = $payment_id;
            $payments = $this->paymentRepo->getByReceipt($receipt_id);
        } else {
            $payments[] = $this->paymentRepo->get($payment_id);
        }

        foreach ($payments as $payment) {
            if ($this->checkPaymentPaid($payment)) {
                #todo fire payment paid event
                return true;
            }
        }

        return false;
    }

    protected function checkPaymentPaid(Payment $payment)
    {
        return $payment->isPaid() ?: $this->checkPaidFromGateway($payment);
    }

    protected function checkPaidFromGateway(Payment $payment)
    {
        if ($paid = $this->gateway->transactionIsPaid($payment->getKey())) {
            #todo fire gateway paid event

        }

        return $paid;
    }

    public function checkRefundSucceed($refund_id)
    {
        // TODO: Implement checkRefundSucceed() method.
    }

    /**
     * @param $payment_id
     * @param $amount
     * @param $desc
     */
    public function refund($payment_id, $amount, $desc)
    {
        $payment = $this->paymentRepo->get($payment_id);

        if (intval($amount) > 0 && $payment['amount_refundable'] >= intval($amount)) {
            $refund_charge = $this->gateway->refund($payment, $desc, $amount);
            #todo persist refund payment
            $refund_payment = $refund_charge;
            $payment->setPreRefund($amount);
            return $refund_payment;
        }

        return false;
    }

    public function fetchGatewayTransaction($payment_id)
    {
        return $this->gateway->fetchTransaction($payment_id);
    }

    public function purchase(NeedPay $order, Receipt $receipt, $channel = null)
    {
        $charge = $this->gateway->purchase($order, $channel);
        $payment = $this->paymentRepo->create($receipt, $this->gateway);

        return $charge;
    }
}
