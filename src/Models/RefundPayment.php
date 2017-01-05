<?php namespace BTWay\Payments\Models;

use BTWay\Payments\Events\Payments\PaymentRefundSucceed;
use BTWay\Payments\Events\Payments\PaymentRefundFailed;
use BTWay\Payments\Protocol;
use Illuminate\Database\Eloquent\Model;

class RefundPayment extends Model {

    public $incrementing = false;

    protected $table = 'refund_payments';

    protected $guarded = [];

    protected $dates = [
        'time_succeed',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function receipt()
    {
        return $this->hasOne(RefundReceipt::class, 'refund_payment_id', 'id');
    }

    public function isSucceed()
    {
        return $this->getAttributeValue('succeed') ?: false;
    }

    public function getAmount()
    {
        return $this->getAttributeValue('amount');
    }

    //operation
    public function setAsSucceed($transaction_no, $time_succeed = null)
    {
        if (!$this->isSucceed()) {
            $this->setAttribute('transaction_no', $transaction_no);
            $this->setAttribute('time_succeed', $time_succeed);
            $this->setAttribute('status', Protocol::STATUS_REFUND_REFUNDED);
            $this->save();
        }

        $receipt = $this->receipt;
        $receipt->setAsSucceed();

        event(new PaymentRefundSucceed($this));
    }

    public function setAsFail($failure_code, $failure_msg)
    {
        $this->setAttribute('failure_code', $failure_code);
        $this->setAttribute('failure_msg', $failure_msg);
        $this->setAttribute('status', Protocol::STATUS_REFUND_FAIL);
        $this->save();

        $receipt = $this->receipt;
        $receipt->setAsFail();

        event(new PaymentRefundFailed($this));
    }

}
