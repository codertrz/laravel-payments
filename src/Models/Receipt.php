<?php namespace Beansme\Payments\Models;

use Beansme\Payments\Events\Receipts\ReceiptPaid;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Contracts\CanRefund;
use Beansme\Payments\Services\Contracts\NeedPay;
use Beansme\Payments\Services\Gateways\Exceptions\ChargeNotPayException;
use Beansme\Payments\Services\HelperAbstract;
use Hafael\LaraFlake\Traits\LaraFlakeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model {

    use SoftDeletes, LaraFlakeTrait, CanRefund;

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

    public function getPaymentId()
    {
        return $this->getAttributeValue('payment_id');
    }

    public function getAmount()
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

    public function getPayerID($type = Protocol::PAYER_ID_USER_ID)
    {
        $user_id = $this->getAttributeValue('user_id');
        if ($type == Protocol::PAYER_ID_OPEN_ID) {
            $getUserOpenidFunction = config('payments.helper_functions.get_user_openid');
            return call_user_func($getUserOpenidFunction);
        }

        return $user_id;
    }


    public function setAmountAttribute($amount)
    {
        $this->setAttribute('amount', $amount);
        $this->setAttribute('refundable_amount', $amount);
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
    public function setAsPaid(Payment $payment)
    {
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


    /**
     *
     */
    public function getRefundNo()
    {
        return $this->getPaymentId();
    }
}
