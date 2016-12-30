<?php namespace BTWay\Payments\Models;

use BTWay\Payments\Events\Payments\PaymentPaid;
use BTWay\Payments\Services\Contracts\CanRefund;
use BTWay\Payments\Services\Contracts\PaymentForReceipt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model {

    use SoftDeletes, CanRefund, PaymentForReceipt;

    public $incrementing = false;

    protected $table = 'payments';

    protected $guarded = [];

    protected $dates = [
        'time_paid',
        'time_expire',
        'time_settle',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    protected $casts = [
        'credential' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id', 'id');
    }

    public function isPaid()
    {
        return $this->getAttributeValue('paid') ? true : false;
    }

    //operation
    public function setAsPaid($transaction_no, $time_paid = null)
    {
        if (!$this->isPaid()) {
            $this->setAttribute('transaction_no', $transaction_no);
            $this->setAttribute('time_paid', (is_null($time_paid) ? Carbon::now() : Carbon::createFromTimestamp($time_paid)));
            $this->setAttribute('paid', true);
            $this->save();
        }

        event(new PaymentPaid($this));
    }


    public function getRefundNoKey()
    {
        return $this->getKey();
    }

    public function getRefundPaymentsName()
    {
        return RefundPayment::class;
    }

    public function getGateway()
    {
        return $this->getAttributeValue('gateway');
    }

    public function getApp()
    {
        return $this->getAttributeValue('app');
    }

    public function getChannel()
    {
        return $this->getAttributeValue('channel');
    }

    public function getPaymentId()
    {
        return $this->getKey();
    }

    public function getTransactionNo()
    {
        return $this->getAttributeValue('transaction_no');
    }

    public function getCurrency()
    {
        return $this->getAttributeValue('currency');
    }

    public function getTimePaid()
    {
        return $this->getAttributeValue('time_paid');
    }
}
