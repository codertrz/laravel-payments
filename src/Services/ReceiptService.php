<?php namespace Beansme\Payments\Services;

use Beansme\Payments\Repositories\PaymentRepoContract;
use Beansme\Payments\Repositories\ReceiptRepoContract;
use Beansme\Payments\Services\Contracts\NeedPay;
use Beansme\Payments\Services\Gateways\GatewayAbstract;

class ReceiptService implements ReceiptServiceContract {

    /**
     * @var PaymentRepoContract
     */
    private $paymentRepo;
    /**
     * @var ReceiptRepoContract
     */
    private $receiptRepo;

    /**
     * ReceiptService constructor.
     * @param PaymentRepoContract $paymentRepo
     * @param ReceiptRepoContract $receiptRepo
     */
    public function __construct(PaymentRepoContract $paymentRepo, ReceiptRepoContract $receiptRepo)
    {
        $this->paymentRepo = $paymentRepo;
        $this->receiptRepo = $receiptRepo;
    }

    public function checkout(NeedPay $order, GatewayAbstract $gateway, $extra = [])
    {
        $receipt = $this->receiptRepo->getByOrder($order);
        if ($this->checkPaid($receipt)) {
            return $gateway->fetchTransaction($receipt->getPayment());
        }
        //发起支付
        $charge = $gateway->purchase($order);
        //本地存储
        $payment = $gateway->persistTransaction($charge, $receipt);

        return $charge;
    }

    public function checkPaid($receipt_id)
    {
        // TODO: Implement checkPaid() method.
    }

    public function refund($receipt_id)
    {
        // TODO: Implement refund() method.
    }

    public function checkRefund($receipt_id)
    {
        // TODO: Implement checkRefund() method.
    }


}
