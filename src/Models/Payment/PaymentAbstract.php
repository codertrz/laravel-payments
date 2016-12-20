<?php namespace Beansme\Payments\Models\Payments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class PaymentAbstract extends Model {

    use SoftDeletes;

    protected $table = 'payments';

    protected $guarded = [];

    public function billing()
    {
        return $this->morphTo('billing');
    }

}
