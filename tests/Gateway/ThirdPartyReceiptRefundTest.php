<?php namespace BTWay\Payments\Test\Gateway;


use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Models\RefundPayment;
use BTWay\Payments\Models\RefundReceipt;
use BTWay\Payments\Protocol;
use BTWay\Payments\Services\Gateways\Exceptions\CanNotRefundException;
use BTWay\Payments\Services\Gateways\Exceptions\ChargeNotPayException;

class ThirdPartyReceiptRefundTest extends ThirdPartyReceiptTest {

    /** @test */
    public function it_can_apply_a_refund_request_fail()
    {
        $receipt = $this->it_can_finish_purchase_process_by_check();

        $this->checkPaid($receipt['payment_id']);

        $this->expectException(CanNotRefundException::class);
        $this->pay->refund($receipt['order_no'], $receipt['amount'] + 1, 'test');
    }

    /** @test */
    public function it_can_apply_a_refund_request()
    {
        $receipt = $this->it_can_finish_purchase_process_by_check();

        $this->app['config']->set('payments.refund_audit', true);

        $this->checkPaid($receipt['payment_id']);

        $refund_receipt = $this->pay->refund($receipt['order_no'], $receipt['amount'], 'test');

        $this->assertTrue($refund_receipt instanceof RefundReceipt);

        $this->seeInDatabase('refund_receipts', [
            'id' => $refund_receipt['id'],
            'order_no' => $receipt['order_no'],
            'user_id' => $receipt['user_id'],
            'receipt_id' => $receipt['id'],
            'status' => Protocol::STATUS_REFUND_APPLY
        ]);


        $refund_receipt = $this->pay->approveRefund($refund_receipt);


        $this->seeInDatabase('receipts', [
            'id' => $refund_receipt['receipt_id'],
            'refund_status' => Protocol::STATUS_REFUND_EXIST
        ]);

        $this->seeInDatabase('refund_receipts', [
            'id' => $refund_receipt['id'],
            'status' => Protocol::STATUS_REFUND_REFUNDING,
        ]);

        $this->seeInDatabase('refund_payments', [
            'id' => $refund_receipt->getRefundPaymentId(),
            'payment_id' => $refund_receipt->getPaidPaymentId(),
            'succeed' => false,
            'amount' => $refund_receipt['amount'],
        ]);

        $this->assertTrue($this->pay->checkRefundSucceed($refund_receipt));

        $this->seeInDatabase('refund_payments', [
            'id' => $refund_receipt->getRefundPaymentId(),
            'payment_id' => $refund_receipt->getPaidPaymentId(),
            'succeed' => true,
            'amount' => $refund_receipt['amount'],
        ]);

        $this->seeInDatabase('refund_receipts', [
            'id' => $refund_receipt['id'],
            'status' => Protocol::STATUS_REFUND_REFUNDED,
        ]);

        $this->seeInDatabase('receipts', [
            'id' => $refund_receipt['receipt_id'],
            'refund_status' => Protocol::STATUS_REFUND_DONE
        ]);

    }

}
