<?php namespace Beansme\Payments\Models;

use Beansme\Payments\Events\Receipts\ReceiptPaid;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Contracts\NeedPay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model {

    use SoftDeletes;

    public $incrementing = false;

    protected $table = 'receipts';

    protected $guarded = [];

    protected $dates = [
        'time_paid',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    //成功支付记录
    public function payment()
    {
        return $this->hasOne(Payment::class, 'id', 'payment_id');
    }

    //发起支付记录
    public function payments()
    {
        return $this->hasMany(Payment::class, 'receipt_id', 'id');
    }

    public function getPayment()
    {
        return $this->getAttributeValue('payment_id');
    }

    public function getAmount()
    {
        return $this->getAttributeValue('amount');
    }

    /**
     * 判断
     */

    public function isPaid()
    {
        return $this->getAttributeValue('pay_status') == Protocol::STATUS_PAY_PAID;
    }

    public function hasRefund()
    {
        return $this->getAttributeValue('refund_status') != Protocol::STATUS_REFUND_NONE;
    }

    //操作
    public function setAsPaid(Payment $payment)
    {
        if (!$payment->isPaid()) {
            throw new \Exception('payment not paid : ' . $payment->toJson(), 400);
        }

        if (!$this->isPaid()) {
            $this->setAttribute('gateway', $payment->getAttributeValue('gateway'));
            $this->setAttribute('app', $payment->getAttributeValue('app'));
            $this->setAttribute('channel', $payment->getAttributeValue('channel'));
            $this->setAttribute('payment_id', $payment->getAttributeValue('id'));
            $this->setAttribute('transaction_no', $payment->getAttributeValue('transaction_no'));
            $this->setAttribute('currency', $payment->getAttributeValue('currency'));
            $this->setAttribute('time_paid', $payment->getAttributeValue('time_paid'));
            $this->setAttribute('pay_status', Protocol::STATUS_PAY_PAID);
            $this->save();
        }

        event(new ReceiptPaid($this));
    }


}
