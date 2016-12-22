<?php namespace Beansme\Payments\Models;

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

    //operation

}
