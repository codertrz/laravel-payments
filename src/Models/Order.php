<?php namespace Beansme\Payments\Models;
use Beansme\Payments\Services\NeedPay;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    use NeedPay;

}
