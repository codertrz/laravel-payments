<?php namespace Beansme\Payments\Models;

use Beansme\Payments\Events\Receipts\ReceiptPaid;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Contracts\PaymentForReceipt;
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

    //发起支付记录
    public function payments()
    {
        return $this->hasMany($this->getAttributeValue('payment_type'), PaymentForReceipt::getReceiptKeyName(), $this->getKeyName());
    }

    //成功支付记录
    public function payment()
    {
        return $this->morphTo();
    }

    protected function getPaymentKeyName()
    {
        return 'payment_id';
    }

    public function getPaymentId()
    {
        return $this->getAttributeValue($this->getPaymentKeyName());
    }

    public function getPaymentAmount()
    {
        return $this->getAttributeValue('amount');
    }

    public function getOrderNo()
    {
        return $this->getAttributeValue('order_no');
    }

    public function getPaymentSubject()
    {
        return $this->getOrderNo();
    }

    public function getPaymentBody()
    {
        return $this->getOrderNo();
    }

    protected function getPayerKeyName()
    {
        return 'user_id';
    }

    public function getPaymentPayerID($type = Protocol::PAYER_ID_USER_ID)
    {
        $user_id = $this->getAttributeValue($this->getPaymentKeyName());
        if ($type == Protocol::PAYER_ID_OPEN_ID) {
            $getUserOpenidFunction = config('payments.helper_functions.get_user_openid');
            return call_user_func($getUserOpenidFunction);
        }

        return $user_id;
    }

    /**
     * 判断
     */
    public function isPaid()
    {
        return $this->getAttributeValue('pay_status') == Protocol::STATUS_PAY_PAID;
    }

    /**
     * 是否可以发起退款
     * @return bool
     */
    public function canRefund()
    {
        return $this->getAttributeValue('amount_refunded') < $this->getAttributeValue('amount');
    }

    public function hasRefund()
    {
        return $this->getAttributeValue('refund_status') != Protocol::STATUS_REFUND_NONE;
    }

    //操作
    public function setAsPaid(PaymentForReceipt $payment)
    {
        if (!$this->isPaid()) {
            $this->setAttribute('gateway', $payment->getGateway());
            $this->setAttribute('app', $payment->getApp());
            $this->setAttribute('channel', $payment->getChannel());
            $this->setAttribute('payment_id', $payment->getPaymentId());
            $this->setAttribute('transaction_no', $payment->getTransactionNo());
            $this->setAttribute('currency', $payment->getCurrency());
            $this->setAttribute('time_paid', $payment->getTimePaid());
            $this->setAttribute('pay_status', Protocol::STATUS_PAY_PAID);
            $this->save();
        }

        event(new ReceiptPaid($this));
    }


}
