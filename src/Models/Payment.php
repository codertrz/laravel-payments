<?php namespace Beansme\Payments\Models;

use Beansme\Payments\Events\Payments\PaymentPaid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model {

    use SoftDeletes;

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

    public function refunds()
    {
        return $this->hasMany(RefundPayment::class, 'payment_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id', 'id');
    }

    public function isPaid()
    {
        return $this->getAttributeValue('paid');
    }

    //operation
    public function setAsPaid($transaction_no, $time_paid = null)
    {
        if (!$this->isPaid()) {
            $this->setAttribute('transaction_no', $transaction_no);
            $this->setAttribute('time_paid', (is_null($time_paid) ? Carbon::now() : Carbon::parse($time_paid)));
            $this->setAttribute('paid', true);
            $this->save();
        }
        event(new PaymentPaid($this));
    }

}
