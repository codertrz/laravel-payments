<?php namespace BTWay\Payments\Models;

use BTWay\Payments\Events\Receipts\ReceiptPaid;
use BTWay\Payments\Protocol;
use BTWay\Payments\Services\Contracts\PaymentForReceipt;
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

    public function refunds()
    {
        return $this->hasMany(RefundReceipt::class, 'receipt_id', 'id');
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

    public function setAsPaid($payment)
    {
        if (!$this->isPaid()) {
            $this->setAttribute('gateway', $payment->getGateway());
            $this->setAttribute('app', $payment->getApp());
            $this->setAttribute('channel', $payment->getChannel());
            $this->setAttribute('payment_id', $payment->getPaymentId());
            $this->setAttribute('payment_no', $payment->getPaymentNo());
            $this->setAttribute('transaction_no', $payment->getTransactionNo());
            $this->setAttribute('currency', $payment->getCurrency());
            $this->setAttribute('time_paid', $payment->getTimePaid());
            $this->setAttribute('pay_status', Protocol::STATUS_PAY_PAID);
            $this->save();
        }

        event(new ReceiptPaid($this));
    }


    /**
     * 是否可以发起退款
     * @param int|null $request_amount
     * @return bool
     */
    public function canRefund($request_amount = null)
    {
        $request_amount = is_null($request_amount)
            ? $this->getCanRefundAmount()
            : intval($request_amount);
        return $this->isPaid() && ($request_amount <= $this->getCanRefundAmount());
    }

    public function getCanRefundAmount()
    {
        return $this->getAttributeValue('amount_refundable');
    }

    public function setApplyRefund($amount, $desc)
    {
        $refund_receipt = $this->refunds()->create([
            'id' => Protocol::generateId(),
            'amount' => $amount,
            'order_no' => $this->getOrderNo(),
            'user_id' => $this->getAttributeValue('user_id'),
            'desc' => $desc,
            'paid_payment_id' => $this->getPaymentId(),
            'status' => Protocol::STATUS_REFUND_APPLY,
        ]);

        $this->attributes['amount_refundable'] = $this->attributes['amount_refundable'] - $amount;
        $this->setAttribute('refund_status', Protocol::STATUS_REFUND_EXIST);

        $this->save();

        return $refund_receipt;
    }

    public function setRejectRefund(RefundReceipt $refund)
    {
        $this->attributes['amount_refundable'] = $this->attributes['amount_refundable'] + $refund->getAmount();
        $this->save();
    }


    public function setAsRefunded(RefundReceipt $refund)
    {
        $this->attributes['amount_refunded'] = $this->attributes['amount_refunded'] + $refund->getAmount();

        if ($this->getCanRefundAmount() <= 0 && $this->refunds()->where('status', Protocol::STATUS_REFUND_REFUNDED)->sum('amount') == $this->attributes['amount_refunded']) {
            $this->attributes['refund_status'] = Protocol::STATUS_REFUND_DONE;
        }

        $this->save();
    }


}
