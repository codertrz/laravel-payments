<?php namespace Beansme\Payments\Models;

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

    //operation

}
