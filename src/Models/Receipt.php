<?php namespace Beansme\Payments\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model {

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


}
