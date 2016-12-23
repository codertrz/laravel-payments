<?php namespace Beansme\Payments\Services;
use Beansme\Payments\Repositories\PaymentRepoContract;
use Beansme\Payments\Repositories\RefundRepoContract;
use Omnipay\Omnipay;

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
     * PaymentService constructor.
     * @param PaymentRepoContract $paymentRepo
     * @param RefundRepoContract $refundRepo
     */
    public function __construct(PaymentRepoContract $paymentRepo, RefundRepoContract $refundRepo)
    {
        $this->paymentRepo = $paymentRepo;
        $this->refundRepo = $refundRepo;
    }

    public function checkPaidSucceed($payment_id)
    {

    }

    public function checkRefundSucceed($refund_id)
    {
        // TODO: Implement checkRefundSucceed() method.
    }

    public function refund($payment_id, $amount)
    {
        // TODO: Implement refund() method.
    }


}
