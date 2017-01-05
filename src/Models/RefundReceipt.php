<?php namespace BTWay\Payments\Models;

use BTWay\Payments\Protocol;
use Illuminate\Database\Eloquent\Model;

class RefundReceipt extends Model {

    protected $table = 'refund_receipts';

    protected $guarded = [];

    public $incrementing = false;

    public function refund()
    {
        return $this->hasOne(RefundPayment::class, 'id', 'refund_payment_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'id', 'paid_payment_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id', 'id');
    }

    public function canRequest()
    {
        return in_array($this->attributes['status'], [
            Protocol::STATUS_REFUND_FAIL,
            Protocol::STATUS_REFUND_APPLY,
        ]);
    }

    public function isSucceed()
    {
        return $this->attributes['status'] == Protocol::STATUS_REFUND_REFUNDED;
    }

    public function getRefundPaymentId()
    {
        return $this->getAttributeValue('refund_payment_id');
    }

    public function getPaidPaymentId()
    {
        return $this->getAttributeValue('paid_payment_id');
    }

    public function getAmount()
    {
        return $this->getAttributeValue('amount');
    }

    public function getDesc()
    {
        return $this->getAttributeValue('desc');
    }


    public function setAsReject($memo)
    {
        $this->setAttribute('memo', $memo);
        $this->setAttribute('status', Protocol::STATUS_REFUND_REJECT);

        $receipt = $this->receipt;
        $receipt->setRejectRefund($this);

        $this->save();
    }

    public function setAsFail($failure_code = null)
    {
        $this->setAttribute('status', Protocol::STATUS_REFUND_FAIL);
        $this->setAttribute('failure_code', $failure_code);
        $this->save();
    }

    public function setAsApprove($payment_id)
    {
        $this->setAttribute('status', Protocol::STATUS_REFUND_REFUNDING);
        $this->setAttribute('refund_payment_id', $payment_id);
        $this->save();
    }

    public function setAsSucceed()
    {
        if (!$this->isSucceed()) {
            $this->setAttribute('status', Protocol::STATUS_REFUND_REFUNDED);
            $this->save();
            $receipt = $this->receipt;
            $receipt->setAsRefunded($this);
        }
    }

}
